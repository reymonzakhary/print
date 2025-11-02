<?php


namespace Modules\Cms\Foundation\Traits;

use Modules\Cms\Foundation\Directives\DirectivesFactory;

trait HasDirectives
{
    /** 
     * @param $content
     * @return string
     * replace the directives from the chunk with hidden inputs
    */
    private function replaceChunkDirectives(string $content, array $data = [])
    {
        return preg_replace_callback('/\[\[@(.*?)\]\]/sm', function($match) use ($data) {
            $html = '';

            $html .= DirectivesFactory::make($match[1], $this->data??$data)?->getDirectiveContent();

            foreach ($this->inputs->validation_messages??[] as $messages) {
                foreach ($messages as $key => $message) {
                    $html .= "<input type=\"hidden\" name=\"__data[validation_messages][{$key}]\" value=\"{$message}\" />\n";
                }
            }

            foreach ($this->inputs->validation_rules??[] as $messages) {
                foreach ($messages as $key => $messages) {
                    foreach ($messages as $message) {
                        $html .= "<input type=\"hidden\" name=\"__data[validation_rules][{$key}]\" value=\"{$message}\" />\n";
                    }
                }
            }

            if ($match[1] == 'errors') {
                $html .= $this->getErrorsHtml();
            }
            return $html;
        }, $content);
    }

    public function replaceSpecificDirective($directive, $content, $data)
    {
        return preg_replace_callback('/\[\[@'.$directive.'\]\]/sm', function ($match) use ($directive, $data) {
            return DirectivesFactory::make($directive, $data)->getDirectiveContent();
        }, $content);
    }
}
