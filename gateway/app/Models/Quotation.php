<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    use HasFactory;

    public $fillable = [
        'hostname_id',
        'internal_id',
        'external_id',
        'company_id',
        'st',
        'contract_id'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Hostname::class, 'hostname_id', 'id', 'quotations');
    }

    /**
     * Get the contract associated with this model.
     *
     * @return BelongsTo
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }
}
