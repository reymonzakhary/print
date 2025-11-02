<?php
namespace Modules\Cms\Foundation\Directives;

use Modules\Cms\Foundation\Contracts\DirectiveContract;

final class DeleteAddressDirective extends DirectiveContract
{
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->command = 'deleteAddress';
    }
}
