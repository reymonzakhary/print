<?php

namespace Modules\Cms\Plugins\Resources;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Modules\Cms\Entities\Chunk;
use Modules\Cms\Entities\Resource as ResourceModel;
use Modules\Cms\Plugins\Resources\Contracts\ResourceRepository;

class Resource implements ResourceRepository
{
    /**
     * outer tpl for ul list
     * @var string
     */
    public string $boxTpl = '';

    public object $resources;

    private static array $output;

    protected function blader(
        string $str,
        array  $data = []
    )
    {
        $empty_filesystem_instance = new Filesystem;
        $blade = new BladeCompiler($empty_filesystem_instance, 'datatables');
        $parsed_string = $blade->compileString($str);
        $__data['__env'] = app(Factory::class);
        ob_start() and extract($data, EXTR_SKIP);
        try {
            eval('?>' . $parsed_string);
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        $str = ob_get_contents();

        ob_end_clean();
        return $str;
    }


    public function __invoke(
        string $method,
        array  $args
    ): mixed
    {
        return $this->{$method}(...$args);
    }

    public static function render(
        string $html
    ): string
    {
        $string = preg_replace_callback('/\[\[!resources?[^"]+]]/', function ($match) {
            if (count($match) > 0) {
                $match = str_replace(['[[', ']]', '`', '!resources?'], ['', '', '', ''], $match[0]);
                parse_str($match, $output);
                $output = array_map('trim', $output);
                self::$output = $output;
                return (new self)->getHtml($output)->toResource();
            }
        }, $html);
        return $string;
    }

    final public function getResource(): object
    {
        $resource_ids = ResourceModel::whereDoesntHave('groups')->where([['language', app()->getLocale()]])->pluck('resource_id')->toArray();
        if (auth()->check()) {
            $userResources = auth()->user()->user_resources->pluck('resource_id')->toArray();
            $resource_ids = array_merge(array_values($resource_ids), array_values($userResources));
        }
        $this->resources = ResourceModel::where([
            ['language', app()->getLocale()],
            ['published', true],
            ['hidden', false]
        ])->whereIn('resource_id', $resource_ids);
        if (optional(self::$output)['ids']) {
            $ids = explode(',', self::$output['ids']);
            $this->resources = $this->resources->whereIn('base_id', $ids);
        } else {
            $start = optional(self::$output)['start'] ?? 0;
            $this->resources = $this->resources->where('parent_id', $start);
        }
        $this->resources = $this->resources->with(['children'])
            ->orderBy(self::$output['sort'] ?? 'sort', self::$output['sortDir'] ?? 'ASC')
            ->get();
        return $this->resources;
    }


    public function toResource()
    {
        $resources = $this->getResource();
        $tpl = '';
        foreach ($resources as $resource) {
            $tpl .= $this->blader($this->boxTpl, $resource->toArray());
        }
        return $tpl;

//        while($this->continue) {
//        }
    }

    /**
     * @param array $menu
     */
    protected function getHtml(
        array $menu
    ): self
    {
        collect($menu)->map(function ($v, $k) use ($menu) {
            if (method_exists($this, $k)) {
                return $this->{$k}($v);
            }
        });
        return $this;
    }

    public function boxTpl(
        string $string
    ): void
    {
        $this->boxTpl = htmlspecialchars_decode(optional(Chunk::where('name', $string)->first())->content);
    }

    public function boxClass(): string
    {
        // TODO: Implement rowClass() method.
    }

    public function classes(): string
    {
        // TODO: Implement classes() method.
    }
}
