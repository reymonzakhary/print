<?php

namespace App\Models;

use Hyn\Tenancy\Contracts\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationCountry extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'active'
    ];

    /**]
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the hostname associated with this entity.
     *
     * @return BelongsTo
     */
    public function hostname(): BelongsTo
    {
        return $this->belongsTo(Hostname::class);
    }

}
