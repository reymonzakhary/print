<?php

namespace App\Blueprints\Contracts\Traits;

use App\Foundation\Status\Status;

trait HandleStatus
{

    public function bootStatus(): void
    {
        if ($this->request->get('status_from') && $this->request->get('attachment_destination') !== 'request') {
            $status = match ($this->last) {
                false => Status::PENDING,
                true => Status::NEW,
                default => Status::IN_PROGRESS,
            };
            $this->request->get('attachment_destination')->update([
                'st' => $status
            ]);
        }
    }
}
