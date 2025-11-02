<?php

namespace Modules\Cms\Foundation\Traits;

trait HasCustomHtmlAttributes
{
    /** 
     * @param $content
     * @return string
     * replace the custom attributes in html
    */
    private function getDataFromHtmlCustomAttributes($content)
    {
        return preg_replace_callback('/(?:data-validation-(rules|messages))="([^"]*)"/m', function($match){
            if ($match[1] === 'messages') {
                $this->inputs->validation_messages[] = $this->getValidationMessages($match[2]);
            }

            if ($match[1] === 'rules') {
                $this->inputs->validation_rules[] = $this->getValidationRules($match[2]);
            }
            return '';
        }, $content);
    }

    /** 
     * @param $data
     * @return array
     * get validation message from the custom attribute
    */
    private function getValidationMessages($data): array
    {
        $messages = explode('|', $data);
        $inputs = [];
        if (count($messages)){
            foreach ($messages as $message) {
                if (str_contains($message, ':')) {
                    $message = explode(':', $message);
                    $inputs[$message[0]] = $message[1];
                }
            }
            return $inputs;
        }
        return [];
    }

    /** 
     * @param $data
     * @return array
     * get validation rules from the custom attribute
    */
    private function getValidationRules($data): array
    {
        $messages = explode('|', $data);
        $inputs = [];
        if (count($messages)){
            foreach ($messages as $message) {
                if (str_contains($message, '.')){
                    $message = explode('.', $message);
                    $inputs[$message[0]][] = $message[1];
                }
            }
            return $inputs;
        }
        return [];
    }
}
