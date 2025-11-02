<?php

namespace App\Jobs\Tenant\Categories;

use App\Models\Hostname;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SharedCategoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected readonly Hostname $tenant,
        protected readonly array $category,
        protected readonly bool $shared
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $custom_fields = $this->tenant->custom_fields;

        $idKey = '_id';
        $shared_categories_key = 'shared_categories';
        $sharedCategory = $custom_fields->pick($shared_categories_key);

        // Search for the item with a specific `_id`
        $result = collect($sharedCategory)->first(function ($item) use($idKey) {
            return $item[$idKey] === $this->category[$idKey];
        });

        // If the category is shared and does not exist in shared categories, add it.
        if ($this->shared && !$result) {
            $sharedCategory = array_merge(
                $sharedCategory,
                [collect($this->category)->only($idKey,'display_name')->toArray()]
            );
        }

        // If the category is not shared and exists in shared categories, remove it.
        if(!$this->shared && $result) {
            $sharedCategory = collect($sharedCategory)->filter(function ($item) use($idKey) {
                return $item[$idKey] !== $this->category[$idKey];
            })->toArray();
        }

        // Update the shared_categories
        $custom_fields->add($shared_categories_key, $sharedCategory);

        $this->tenant->custom_fields = $custom_fields->toArray();
        $this->tenant->save();
    }
}
