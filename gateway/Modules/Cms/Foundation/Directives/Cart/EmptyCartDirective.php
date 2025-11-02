<?php

namespace Modules\Cms\Foundation\Directives\Cart;

class EmptyCartDirective extends CartDirective
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->command = 'emptyCart';
    }
}
