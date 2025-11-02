<?php

namespace App\Blueprints\Contracts\Traits;

use App\Events\Tenant\Blueprints\NotificationBlueprintProgressEvent;

trait HasNotification
{

    public function notifyProgress(): void
    {
        event(new NotificationBlueprintProgressEvent($this->signature, $this->job->model , $this->request->product->name, $this->request->get('attachment_destination'), $this->id, $this->total));
    }
}
