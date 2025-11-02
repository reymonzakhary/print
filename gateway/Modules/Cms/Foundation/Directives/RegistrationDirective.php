<?php
namespace Modules\Cms\Foundation\Directives;

use Modules\Cms\Foundation\Contracts\DirectiveContract;

final class RegistrationDirective extends DirectiveContract
{

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->command = 'registration';
    }

}
