<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Media\MediaSource;
use App\Models\Tenant\Trait\HasAddresses;
use App\Models\Traits\GenerateIdentifier;
use App\Services\Tenant\Categories\CategoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laratrust\Models\Team as TeamModel;
use Modules\Cms\Entities\ResourceGroup;

class Team extends TeamModel
{
    use GenerateIdentifier, HasAddresses;

    /**
     * @var array
     */
    public $guarded = [];

    /**
     * @throws ValidationException
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        Validator::make(
            [
                'team_id' => $value
            ],
            [
                'team_id' => 'integer|min:1'
            ]
        )->validate();

        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Append custom attributes to the model's array form.
     *
     * @var array
     */
    protected $appends = ['print_categories'];

    /**
     * @return BelongsToMany
     */
    public function resourceGroups(): BelongsToMany
    {
        return $this->belongsToMany(ResourceGroup::class, 'user_team_resource_groups');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_teams')
            ->whereHas('contexts', function(Builder $builder) {
                return $builder->where('member', '=', false);
            });
    }

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_teams')
            ->whereHas('contexts', function(Builder $builder) {
                return $builder->where('member', '=', true);
            });
    }

    /**
     * @return MorphToMany
     */
    public function mediaSources(): MorphToMany
    {
        return $this->morphedByMany(MediaSource::class, 'accessable')
            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id', 'team_id');
    }

    /**
     * @return MorphToMany
     */
    public function category(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'accessable','accessables')
            ->using(Accessable::class)
            ->withTimestamps();
    }

    /**
     * @return MorphToMany|void
     */
    public function externalCategory()
    {
        if (in_array(request()->method(), ['POST', 'PUT','PATCH','DELETE'])) {
             return $this->morphedByMany(
                Category::class,
                'accessable',
                'accessables',
                'team_id',
                'external_accessable_id')->using(Accessable::class)
                ->withTimestamps();
        }
    }

    /**
     * @return Collection
     */
    public function getPrintCategoryData(): Collection
    {
        // Example service call, replace with actual logic.
        $externalIds = $this->accessables()
            ->whereNotNull('external_accessable_id')
            ->pluck('external_accessable_id')
            ->toArray();

        $response = app(CategoryService::class)->getCategories($externalIds);

        if(is_array(optional($response)['data']??[])) {
            return collect(optional($response)['data']??[]);
        }
        return collect( json_decode(optional($response)['data']??[], true) ?? []);
    }

    /**
     * Load both categories and external categories.
     *
     * @return array|Collection
     */
    public function getPrintCategoriesAttribute(): array|Collection
    {
        return  $this->getPrintCategoryData();
    }

    /**
     * @return HasMany
     */
    public function accessables(): HasMany
    {
        return $this->hasMany(Accessable::class, 'team_id', 'id');
    }

    /**
     * @return MorphToMany
     */
    public function product(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'accessable','accessables')
            ->using(Accessable::class)
            ->withTimestamps();
    }
}
