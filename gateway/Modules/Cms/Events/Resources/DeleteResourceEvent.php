<?php

namespace Modules\Cms\Events\Resources;

use App\Models\Tenants\Language;
use App\Models\Tenants\User;
use Illuminate\Queue\SerializesModels;
use Modules\Cms\Entities\Resource;

class DeleteResourceEvent
{
    use SerializesModels;

    /**
     * @var Resource|Resource
     */
    public Resource $resource;

    /**
     * @var User
     */
    public User $user;

    /**
     * @var Language
     */
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

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
