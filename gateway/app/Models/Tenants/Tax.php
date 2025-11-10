<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tax extends Model
{
    use HasFactory;

    /** @var bool  */
    public $timestamps = false;

    protected $fillable = [
        'percentage', 'default'
    ];

    /**
     * Define a relationship between the current model and the Country model.
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Boot method for the Tax model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($tax) {
            if ($tax->default) {
                $tax
                    ->where('country_id', $tax->country_id)
                    ->update([
                        'default' => false
                    ]);
            }
        });

        static::updating(function ($tax) {
            if ($tax->default) {
                $tax
                    ->where('country_id', $tax->country_id)
                    ->update([
                        'default' => false
                    ]);
            }
        });
    }
}
