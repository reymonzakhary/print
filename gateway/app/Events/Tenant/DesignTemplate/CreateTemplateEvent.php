<?php

namespace App\Events\Tenant\DesignTemplate;

use App\Models\Tenant\DesignProviderTemplate;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateTemplateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public DesignProviderTemplate $designProviderTemplate,
        public string                 $tenant,
        public User                   $user
    ){}

}
