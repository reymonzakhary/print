<?php

declare(strict_types=1);

namespace App\Events\Tenant\Custom;

use App\Models\Tenants\Category;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CreateCategoryEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public readonly Category $category,
        public readonly array $translations = []
    ) {
    }
}
