<?php

namespace App\Models\Tenants;

use App\Foundation\FileManager\Contracts\FileManagerInterface;
use App\Foundation\FileManager\Traits\InteractWithMedia;
use App\Models\Traits\ResolveLanguageRouteBinding;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Brand extends Model implements Sortable, FileManagerInterface
{
    use HasFactory, Slugable, SortableTrait,
        ResolveLanguageRouteBinding,
        InteractWithMedia;

//        InteractsWithMedia;


    /**
     * @var string[]
     */
    protected $fillable = [
        'name', 'slug', 'iso', 'description', 'created_by',
        'published_by', 'published_at', 'sort', 'row_id', 'locked_at', 'locked_by'
    ];
    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];


    public function removeMedia(): self
    {
        $this->media()->detach();
        return $this;
    }

    /**
     *
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('custom-brand')
            ->acceptsMimeTypes(['image/jpeg'])
            ->useDisk('assets')
            ->setConversions(['withs' => '029348234242']);
    }
}
