<?php

namespace Modules\Cms\Foundation\Contracts;

abstract class DirectiveContract
{
    protected $data;
    protected $command;
    
    abstract public function __construct(array $data);
    
    /** 
     * @param void
     * @return string
     * the get the content of the directive
     * to replace the directive by it
    */
    public function getDirectiveContent(): string
    {
        $html = '';
        $html .= "<input type=\"hidden\" name=\"_token\" value='".csrf_token()."' />\n";
        $html .= "<input type=\"hidden\" name=\"__command\" value=\"{$this->command}\" />\n";
        foreach ($this->data as $key => $value) {
            $html .= "<input type=\"hidden\" name=\"__data[{$key}]\" value=\"{$value}\" />\n";
        }

        return $html;
    }
}