<?php

namespace Modules\Cms\Foundation\Compiler;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Entities\Resource;
use Illuminate\Support\Str;
use Modules\Cms\Enums\BlockTypesEnum;
use Modules\Cms\Foundation\Traits\HasCategory;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\HasMedia;
use Modules\Cms\Foundation\Traits\HasRecursiveModels;

class SyntaxAnalyzer
{
    use HasRecursiveModels, HasMedia, HasCategory, HasDirectives;
    private $tokens = [];

    private string $content;

    private string $html;

    private $resource_media;

    private $injected;

    private $injectedModel;

    public function __construct(
        private null|Resource|Model|array $resource
    )
    {
        $this->content = '';
        $this->content = collect();
    }

    public function injectResource($content = null)
    {
        $this->setContent($content);
        $this->injected = true;
        return $this;
    }

    public function injectModel($content = null)
    {
        $this->setContent($content);
        $this->injectedModel = true;
        return $this;
    }

    public function setContent(
        null|string $content
    ): self
    {
        $this->content = $content??$this->resource?->template?->content??'';

        if ($this->resource instanceof Model) {
            $this->resource_media = $this->resource?->media;
        }

        return $this;
    }

    public function parse()
    {

        $this->html = $this->replaceChunkDirectives($this->content);
        $this->html = preg_replace_callback('/\[\[\+is(.*?) (.*?)]]/sm', function ($match) {
            preg_match_all('/:(.*?)=`(.*?)`/sm', $match[2], $params);
            $params = array_combine($params[1], $params[2]);

            $then = optional($params)['then'];
            $else = optional($params)['else'];

            return match ($match[1]) {
                'LoggedIn' => auth()->check()? $then : $else
            };
        }, $this->html);

        $this->html = preg_replace_callback('/\[\[\+(\w*)\:(\w*)\=\`(.*?)\`(.*?)\]\]/sm', function ($match) {
            $value1 = $this->resource->{$match[1]};
            $modifier = $match[2];
            $value2 = $match[3];

            preg_match_all('/:(.*?)=`(.*?)`/sm', $match[4], $params);
            $params = array_combine($params[1], $params[2]);
            
            $can = $this->resolveModifiers($modifier, $value1, $value2);
            
            $then = optional($params)['then'];
            $else = optional($params)['else'];

            $show = trim($match[4]);
            if ($show == ':show' && $can) {
                return $value1;
            } else if ($show == ':hide' && $can) {
                return '';
            }

            return $can? $then : $else;

        }, $this->html);

        $this->html = preg_replace_callback('/\[\[\~\[\[\*(\d*|id)\]\](.*?)\]\]/sm', function ($match) {
            $uri = '';

            if ($match[1] == $this->resource->resource_id || $match[1] == 'id'){
                $uri = $this->resource->uri;
            } else {
                $uri = $this->getResourceFromId($match[1])?->uri;
            }
            
            preg_match_all('/&([^&=]+)=`([^&=]+)`/sm', $match[2], $params);
            $params = array_combine($params[1], $params[2]);
            
            if (strtolower(optional($params)['full']) == 'true') {
                $uri = $this->uriWithFqdn($uri);
            }
            
            return $uri;
        }, $this->html);

        $this->html = preg_replace_callback('/\[\[\~\[\[\+(id)\]\](.*?)\]\]/sm', function ($match) {
            $uri = '';

            if ($match[1] == $this->resource->resource_id || $match[1] == 'id'){
                $uri = $this->resource->uri;
            } else {
                $uri = $this->getResourceFromId($match[1])?->uri;
            }

            preg_match_all('/&([^&=]+)=`([^&=]+)`/sm', $match[2], $params);
            $params = array_combine($params[1], $params[2]);
            
            if (strtolower(optional($params)['full']) == 'true') {
                $uri = $this->uriWithFqdn($uri);
            }

            return $uri;
        }, $this->html);

        $this->html = preg_replace_callback('/\[\[(.*?)]]/sm', function ($match) {
            $command = $match[1];

            if (Str::contains($command, '+errors')) { // skip the error printing tags [[+errors]]
                return '[['.$command.']]';
            }

            $key_operator = substr($command, 0, 1);
            return match ($key_operator) {
                '*', '+' => $this->renderTemplateSyntaxFromCommand($command = $match[1]),
                // '+' => '', // handle the syntax from the passed data
                '!' => '', // handle snippets
                default => $match[0], // return empty string incase of the first letter is not an operator
            };
        }, $this->html);
        return $this;
    }

    /**
     * 
     * interpolate data from the injected data
     * @return self
     */
    public function resolve()
    {
        $this->html = preg_replace_callback('/\[\[(.*?)]]/sm', function ($match) {
            $command = $match[1];

            if (Str::contains($command, '+errors')) { // skip the error printing tags [[+errors]]
                return '[['.$command.']]';
            }
            $key_operator = substr($command, 0, 1);

            return match ($key_operator) {
                '*', '+' => $this->renderTemplateSyntaxFromCommand($command = $match[1]),
                // '+' => '', // handle the syntax from the passed data
                '!' => '', // handle snippets
                default => $match[0], // return empty string incase of the first letter is not an operator
            };
        }, $this->content);
        return $this;
    }

    private function renderTemplateSyntaxFromCommand(
        $command
    ): string
    {

        return preg_replace_callback('/^(\*|\+)(.*)/', function ($match) {
            $identifier = $match[2];

            // explode by . to be able to access nested objects
            $keys_sequence = explode('.', $identifier);

            if ($this->injectedModel) {
                return $this->getObject($this->resource, $keys_sequence);
            }

            $identifier = match ($identifier) {
                'id' => 'resource_id',
                default => $identifier,
            };

            if ($identifier == 'media') {
                $img = $this->resource_media->first();
                if (!$img){
                    return '';
                }

                return Storage::disk('assets')->url(tenant()->uuid . $this->resource?->getImagePath($img?->path, $img?->name));
            }


            if ($keys_sequence[0] == 'block') {
                return $this->getTemplateVariableValue($keys_sequence, $identifier);
            }

            if (!$this->resource->{$identifier} && $this->injected) {
                return '[['.$match[0].']]';
            }

            return $this->getObject($this->resource, $keys_sequence);

        }, $command);
    }

    private function getTemplateVariableValue(
        array $keys_sequence, 
        string $identifier
    ): ?string
    {
        // get parameters from identifier
        preg_match_all('/&([^&=]+)=`([^&=]+)`/sm', $identifier, $params);
        $params = array_combine($params[1], $params[2]);

        if (optional($params)['name']) {
            $type = collect(explode('.', Str::before($identifier, '?')))->last();
            if ($type == 'file') {
                // return s3 path if the type is file
                return $this->getFileUrl(
                    optional(collect($this->resource->content)->where('key', optional($params)['name'])->first())['value']
                );
            }
            return optional(collect($this->resource->content)->where('key', optional($params)['name'])->first())['value'];
        }

        $tv = optional($keys_sequence)[1];
        $block = collect($this->resource->content)->where('key', $tv)->first();

        if (optional($block)['type'] == 'file') {
            return $this->getFileUrl(optional($block)['value']);

        }
        return optional(collect($this->resource->content)->where('key', $tv)->first())['value'];
    }

    private function getFileUrl($path)
    {
        return Storage::disk('assets')->url(tenant()->uuid.'/'.$path);
    }

    public function uriWithFqdn($uri)
    {
        return request()->domain.$uri;
    }

    public function getHtml()
    {
        return htmlspecialchars_decode($this->html);
    }

    private function getResourceFromId($resource_id)
    {
        return Resource::where(['resource_id' => $resource_id, 'language' => app()->getLocale()])->first();
    }

    private function resolveModifiers($modifier, $value1, $value2)
    {
        $value1 = is_bool($value1)? 'true': $value1;

        return match ($modifier) {
            'is','eq' => ($value1 == $value2),
            'ne' => ($value1 != $value2),
            'gte' => ($value1 >= $value2),
            'gt' => ($value1 > $value2),
            'lte' => ($value1 <= $value2),
            'lt' => ($value1 < $value2),
            'contains' => (Str::contains($value1, $value2, true)),
            'containsnot' => (!Str::contains($value1, $value2, true)),
            'in' => (in_array($value1, explode(',', $value2))),
            default => false
        };
    }
}

