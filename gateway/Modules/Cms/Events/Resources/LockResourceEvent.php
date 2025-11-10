<?php

namespace Modules\Cms\Events\Resources;

use App\Models\Tenant\Language;
use App\Models\Tenant\User;
use Illuminate\Queue\SerializesModels;
use Modules\Cms\Entities\Resource;

class LockResourceEvent
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
    )
    {
        $this->resource = $resource;
        $this->user = $user;
    }
}
