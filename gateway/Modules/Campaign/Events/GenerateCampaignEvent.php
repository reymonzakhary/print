<?php

namespace Modules\Campaign\Events;

//use App\Models\Tenants\Campaign;
use App\Models\Tenant\DesignProviderTemplate;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignExport;

class GenerateCampaignEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public Campaign               $campaign,
        public string                 $uuid,
        public string                 $domain,
        public User                   $user,
        public array                  $rows,
        public string                 $time,
        public CampaignExport         $export,
        public DesignProviderTemplate $template
    )
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('campaigns');
    }
}
