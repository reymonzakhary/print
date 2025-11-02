<?php

namespace App\Models\Tenants;

use App\Models\Traits\CanBeScoped;
use App\Models\Traits\HasDynamicValue;
use App\Models\Traits\InteractsWithMedia;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\SortableTrait;

class Setting extends Model
{
    use UsesTenantConnection, CanBeScoped, SortableTrait, HasDynamicValue,
        InteractsWithMedia;

    /**
     * @var array
     */
    protected $fillable = [
        'sort', 'name', 'description', 'key',
        'secure_variable', 'namespace', 'area', 'lexicon', 'value',
        'ctx_id', 'data_type', 'data_variable', 'multi_select', 'incremental'
    ];

    /**
     * @var bool
     */
    public $timestamps = false;


    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    public function getRouteKeyName()
    {
        return 'key';
    }

    public function resolveRouteBinding($value, $field = null)
    {

        if ($field === null) {
            $field = 'key';
        }
        return static::permitted()->where($field, $value)->firstOrFail();
        // assign the result to variable and add more logic here then return
    }

    /**
     * @return BelongsTo
     */
    public function context()
    {
        return $this->belongsTo(Context::class);
    }

    /**
     * @param $value
     * @return ?string
     */
    public function setValueAttribute(
        $value
    ): ?string
    {
        if (method_exists(__class__, "value" . Str::ucfirst($this->attributes['data_type']))) {
            return $this->attributes['value'] = $this->{"value" . Str::ucfirst($this->attributes['data_type'])}($this, $value);
        }
        return $this->attributes['value'] = $value;
    }

    /**
     * @param Builder $builder
     */
    public function scopePermitted(
        Builder $builder
    ): void
    {
        collect(optional(request()->tenant?->configure)['namespaces'])->each(fn($ns) => $builder->orWhere(
            [['namespace', '=', $ns['namespace']], ['area', '=', $ns['area']]])
        );
    }

}
