<?php

namespace Modules\Cms\Events\Resources;

use App\Models\Tenants\Language;
use App\Models\Tenants\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Cms\Entities\Resource;

class UpdateResourceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Resource $resource;

    public User $user;

    public Language $language;

    /**
     * Create a new event instance.
     *
     * @param Resource $resource
     * @param User     $user
     * @param Language $language
     */
    public function __construct(
        Resource $resource,
        User     $user,
        Language $language
    )
    {
        $this->resource = $resource;
        $this->user = $user;
        $this->language = $language;
    }
}
