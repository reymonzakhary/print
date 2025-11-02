<?php

namespace Modules\Cms\Foundation\Directives\Cart;

class AddToCartDirective extends CartDirective
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->command = 'addToCart';
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
        
        if (array_key_exists('category_id', $this->data)){
            if (is_numeric($this->data['category_id'])) {
                $html .= "<input type=\"hidden\" name=\"__data[type]\" value=\"custom\" />\n";
            }else {
                $html .= "<input type=\"hidden\" name=\"__data[type]\" value=\"print\" />\n";
            }
        }

        foreach ($this->data as $key => $value) {
            $html .= "<input type=\"hidden\" name=\"__data[{$key}]\" value='{$value}' />\n";
        }

        return $html;
    }

}
