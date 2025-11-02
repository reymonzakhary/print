<?php

declare(strict_types=1);

namespace App\Events\Tenant\Custom;

use App\Models\Tenants\Box;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class DeleteBoxEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private Box $box)
    {
    }

    public function getBox(): Box
    {
        return $this->box;
    }
}
