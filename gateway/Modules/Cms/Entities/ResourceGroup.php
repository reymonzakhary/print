<?php

namespace Modules\Cms\Entities;

use App\Models\Tenant\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\SortableTrait;

class ResourceGroup extends Model
{
     use SortableTrait;

    protected $fillable = [
        'name', 'sort'
    ];

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    /**
     * @return BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'user_team_resource_groups');
    }

    /**
     * @return BelongsToMany
     */
    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'resource_resource_groups')
            ->where('language', app()->getLocale());
    }
}
