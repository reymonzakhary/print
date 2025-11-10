<?php


namespace Modules\Cms\Compiler;


use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;

abstract class WebAccess
{
    protected ?User $user;

    /**
     * WebAccess constructor.
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }
}
