<?php

declare(strict_types=1);

namespace App\Events\Tenant\Custom;

use App\Models\Tenant\Option;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class DeleteOptionEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private Option $option)
    {
    }

    public function getOption(): Option
    {
        return $this->option;
    }
}
