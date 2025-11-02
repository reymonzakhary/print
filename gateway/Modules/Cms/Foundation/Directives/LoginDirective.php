<?php
namespace Modules\Cms\Foundation\Directives;

use Modules\Cms\Foundation\Contracts\DirectiveContract;

final class LoginDirective extends DirectiveContract
{
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->command = 'login';
    }
}
