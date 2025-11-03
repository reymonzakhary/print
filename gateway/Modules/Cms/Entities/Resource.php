<?php

namespace Modules\Cms\Entities;

use App\Http\Resources\Categories\PrintBoopsResource;
use App\Models\Tenants\Context;
use App\Models\Tenants\User;
use App\Models\Traits\HasChildren;
use App\Models\Traits\InteractsWithMedia;
use App\Models\Traits\ResolveLanguageRouteBinding;
use App\Services\Categories\BoopsService;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JsonException;
use Modules\Cms\Entities\Eloquent\Collection;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Symfony\Component\HttpFoundation\Response;

class Resource extends Model implements Sortable
{
     use  SoftDeletes, SortableTrait,
        InteractsWithMedia, HasRecursiveRelationships;


    /**
     * @return string
     */
    public function getLocalKeyName()
    {
        return 'resource_id';
    }


    /**
     * @var string[]
     */
    protected $fillable = [
        'id', 'base_id', 'title', 'long_title', 'intro_text', 'description', 'menu_title', 'uri',
        'resource_id', 'language', 'content', 'sort', 'isfolder', 'locked', 'published',
        'hidden', 'searchable', 'cacheable', 'hide_children_in_tree',
        'created_by', 'updated_by', 'deleted_by', 'published_by', 'template_id',
        'ctx_id', 'parent_id', 'resource_type_id', 'published_on', 'slug', 'locked_by',
        'category'
    ];

    protected $casts = [
        'content' => AsArrayObject::class
    ];

    protected $data = [
        'published_on'
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
     * @var string[]
     */
    protected $appends = [
        'image'
    ];

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param array $models
     * @return Collection
     */
    public function newCollection(array $models = []): Collection
    {
        return new Collection($models);
    }

    /**
     * @param $value
     * @return mixed
     * @throws JsonException
     */
    public function getContentAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }

        // check if the upcoming data array or not null
        if ($value || is_object($value)) {
            return json_decode($value, true);
        }
        return null;
    }

    /**
     * @param $builder
     * @return mixed
     */
    public function scopeIsParent($builder)
    {
        return $builder->whereNull('parent_id');
    }

    /**
     * @param $value
     * @return array
     */
    public function getImageAttribute($value)
    {
        return count($this->getMedia('main')) > 0 ? $this->getMedia('main')['main'] : [];
    }


    /**
     * @return BelongsTo
     */
    public function lockedby()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * @return BelongsTo
     */
    public function createdby()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function updatedby()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsTo
     */
    public function deletedby()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * @return BelongsTo
     */
    public function publishedby()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * @return BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function context()
    {
        return $this->belongsTo(Context::class, 'ctx_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class, 'resource_type_id');
    }

//    /**
//     * @return BelongsTo
//     */
//    public function parent()
//    {
//        return $this->belongsTo(__CLASS__, 'parent_id', 'id');
//    }



    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    public function hasSubChildren()
    {
        if ($this->hasChildren()) {
            return $this->children()->get()->map(fn($children) => $children->hasChildren())->first();
        }
    }

    /**
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(ResourceGroup::class, 'resource_resource_groups');
    }

    public function getTemplate()
    {
        return $this->template()->first();
    }

    public function isCustomCategory()
    {
        return is_numeric($this->category);
    }
}
