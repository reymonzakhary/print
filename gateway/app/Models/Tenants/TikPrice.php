<?php

namespace App\Models\Tenants;

use App\Models\Traits\GenerateIdentifier;
use App\Models\Traits\HasPrice;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class TikPrice extends Model
{
    use UsesTenantConnection, GenerateIdentifier, HasPrice;

    /**
     * add the relation name for securing the key in db
     * @var string|null
     */
    protected string $relation;

    protected $fillable = [
        'bio_id', 'machine_id', 'from', 'to', 'price', 'active'
    ];
}
