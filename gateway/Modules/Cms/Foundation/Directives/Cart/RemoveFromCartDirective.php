<?php

namespace Modules\Cms\Foundation\Directives\Cart;

class RemoveFromCartDirective extends CartDirective
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->command = 'removeFromCart';
    }
}
