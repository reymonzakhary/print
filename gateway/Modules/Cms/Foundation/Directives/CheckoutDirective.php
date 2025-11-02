<?php

namespace Modules\Cms\Foundation\Directives;

use Modules\Cms\Foundation\Contracts\DirectiveContract;


class CheckoutDirective extends DirectiveContract
{
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->command = 'checkout';
    }

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
            $html .= "<input type=\"hidden\" name=\"__data[{$key}]\" value='{$value}' />\n";
        }

        return $html;
    }
}
