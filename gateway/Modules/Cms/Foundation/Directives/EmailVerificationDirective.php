<?php
namespace Modules\Cms\Foundation\Directives;

use Modules\Cms\Foundation\Contracts\DirectiveContract;

final class EmailVerificationDirective extends DirectiveContract
{
    public function __construct(array $data)
    {
        $this->data = $data;

        $this->command = optional($data)['emailChunk'] ?
            $this->command = 'sendVerificationEmail' 
            : $this->command = 'codeVerification';
    }
}
