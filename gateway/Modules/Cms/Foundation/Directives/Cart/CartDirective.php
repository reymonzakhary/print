<?php

namespace Modules\Cms\Foundation\Directives\Cart;

use Modules\Cms\Foundation\Contracts\DirectiveContract;

abstract class CartDirective extends DirectiveContract
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
