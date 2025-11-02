<?php

namespace Modules\Campaign\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Campaign\Entities\Campaign;

class CreateCampaignFolderEvent
{
    use SerializesModels;

    public Campaign $campaign;
    public string $uuid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        Campaign $campaign,
        string   $uuid
    )
    {
        $this->campaign = $campaign;
        $this->uuid = $uuid;
    }
}
