<?php

namespace Modules\Cms\Foundation\Compiler;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Compilers\BladeCompiler;
use Modules\Cms\Compiler\Chunks\ChunkCompiler;
use Modules\Cms\Foundation\Helpers\Account;

class Compiler
{
    private $content;
    private $syntax;
    /**
     * @param string $content
     * expects html content
    */
    public function __construct(private $resource)
    {
        $this->content = htmlspecialchars_decode($resource?->template?->content??'');
        $this->syntax = new SyntaxAnalyzer($resource);
    }

    /**
     * @return string
     * this function is responsible for compiling
     *  and, render return the content to be sent to the response
     */

    public function compile()
    {
        $content = $this->compileChunk($this->content);
        $content = $this->compileHelperClasses($content);
        $content = $this->compilePrinting($content);
        $content = $this->sanitize($content);
        $content = $this->renderAssets($content);
        $content = $this->syntax->setContent($content)->parse()->getHtml();
        // $content = $this->compileTemplateVariables($content);

        return $content;
    }

    /**
     * @param string|null $str
     * @return string
     * sanitizing the html from any php enjected code
     * must run after rendering the blade
    */
    private function sanitize(string|null $str)
    {

        // sanitize from the @php @endphp tag
        $sanitizedString = preg_replace_callback('/@php(.*?)@endphp/sm', fn ($match) => '', $str);

        // sanitize from @php() tag
        $sanitizedString = preg_replace_callback('/@php[^)]*\([^)]*\)/sm', fn ($match) => '', $sanitizedString);

        // sanitize removing the ->update(), ->delete(), ::update(), ::delete()
        $sanitizedString = preg_replace_callback('/(->|::)\s*(delete|update)\s*\(\)/', fn ($match) => '', $sanitizedString);

        // @TODO remove any namespaces or helpers

        return $sanitizedString;
    }

    /**
     * @param string $content
     * @return string
     * compiles the square bracket syntax
    */
    public static function compileHelperClasses($content)
    {
        $html = $content;

        $html = preg_replace_callback('/\[\[account\.(.*?)\?(.*?)]]/sm', function($match) use ($content) {
            $helper = new Account;

            preg_match_all('/&([^&=]+)=`([^&=]+)`/sm', $match[0], $params);
            $params = array_combine($params[1], $params[2]);

            $helper->_tagname = 'account.'.$match[1];

            foreach ($params as $param => $value) {
                $helper->$param = $value;
            }

            return $helper?->getChunk();
        }, $html);

        $html = preg_replace_callback('/\[\[(\w+)\?(.*?)]]/sm', function($match) use ($content) {

            $class = '\Modules\Cms\Foundation\Helpers\\'.ucfirst(explode('.', $match[1])[0]);
            $helper = null;
            if (class_exists($class)) {
                $helper = new $class;
            }

            preg_match_all('/&([^&=]+)=`([^&=]+)`/sm', $match[0], $params);
            $params = array_combine($params[1], $params[2]);

            if ($helper) {
                $helper->_tagname = $match[1];
                foreach ($params as $param => $value) {
                    $helper->$param = $value;
                }
            }

            return $helper?->getChunk();
        }, $html);

        return $html;
    }

    /**
     * @param string $content
     * @return string
     * compile chunks in the syntax expects string
    */
    private function compileChunk($content)
    {
        return htmlspecialchars_decode(ChunkCompiler::flatHtml($content));
    }

    /**
     * @param string $content
     * @return string
     * expects a string and handle printing the data from data
     * classes with this syntax [[+classname.prop]]
    */
    private function compilePrinting($content)
    {

        return preg_replace_callback('/\[\[\+(.*?)\?(.*?)\]\]$/m', function($match) {
            $classname = $this->getClassName($match[1]);
            $property = $this->getClassProperty($match[1]);

            $class = '\Modules\Cms\Foundation\Helpers\Data\\'.ucfirst($classname);
            // initiate class
            $helper = null;
            if (class_exists($class)) {
                $helper = new $class;
            }

            preg_match_all('/&(\w+)=`(.*?)`/sm', $match[0], $params);
            $params = array_combine($params[1], $params[2]);

            foreach ($params as $param => $value) {
                $helper?->set($param, $value);
            }


            $html = $helper?->getHtml($property);
            return $html;
        }, $content);
    }

    public function compileTemplateVariables($content)
    {
        return preg_replace_callback('/\[\[\*block\.(.*?)\]\]/sm', function ($match) {
            if (str_contains($match[0], '?')) {
                return '';
            }
            $tv = $match[1];
            return optional(collect($this->resource->content)->where('key', $tv)->first())['value'];
        }, $content);
    }

    /**
     * @param string $string
     * @return string
     * get class name from a string
    */
    private function getClassName($string)
    {
        return explode('.', $string)[0];
    }

    /**
     * @param string $string
     * @return string
     * get a property to use from a string
    */
    private function getClassProperty($string)
    {
        $exploded = explode('.', $string);
        if (count($exploded) > 1){
            return $exploded[1];
        }
        return null;
    }


    /**
     * @param String $string
     * @return mixed|String
     */
    protected function renderAssets(
        string $string
    )
    {

        $tenant = request()->tenant->uuid;
        preg_match_all('/(src|href)="([^"]+\.(js|css|png|webp|jpg|jpeg|gif|woff2|woff|html|svg|ico))"/', $string, $source);

        if (count($source) > 0) {
            foreach ($source[2] as $src) {
                if (str_contains($src, '[[++assets]]')) {
                    $path = str_replace(["[[++assets]]", "//"], ["","/"], "{$tenant}/{$src}");
                    if(Storage::disk('assets')->exists($path)) {
                        $string = str_replace('"' . $src . '"', '"' .Storage::disk('assets')->url($path) . '"', $string);
                    }
                }
            }
        }

        return $string;

    }
}
