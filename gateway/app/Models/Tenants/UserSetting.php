<?php

namespace App\Models\Tenants;

use App\Models\Traits\CanBeScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\SortableTrait;

class UserSetting extends Model
{
    use SortableTrait, CanBeScoped;

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'sort', 'name', 'description', 'key',
        'secure_variable', 'namespace', 'area', 'lexicon', 'value',
        'ctx_id', 'data_type', 'data_variable', 'multi_select', 'incremental',
    ];

    /**
     *
     */
    public static function boot()
    {
        parent::boot();
        self::updated(function($model) {
           if($model->key === 'manager_language') {
               app()->setLocale(Str::lower($model->value));
           }
        });
    }

    /**
     * @param mixed $value
     * @param null  $field
     * @return Model|void|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where([['user_id', auth()->id()], ['key', $value]])->first() ??
            abort(404, __('Not Found -- There is no order found'));
    }

    /**
     * @return BelongsTo
     */
    public function users()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany
     */
    public function context()
    {
        return $this->belongsToMany(Context::class);
    }
}
