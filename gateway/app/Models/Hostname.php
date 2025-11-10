<?php

namespace App\Models;

use App\Casts\Hostname\CustomFieldCast;
use App\Foundation\ContractManager\Traits\HasContract;
use App\Models\Tenant\Setting;
use App\Models\Traits\CanBeScoped;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ramsey\Uuid\Uuid;
use \App\Models\Tenant\Company;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Hostname extends Model
{
    use CanBeScoped, HasContract;

    /**
     * @var string
     */
    protected $table = 'hostnames';

    /**
     * @var string[]
     */
    protected $fillable = ['configure', 'supplier', 'logo', 'host_id', 'primary', 'custom_fields', 'fqdn'];

    /**
     * @var string[]
     */
    protected $casts = [
        'configure' => AsArrayObject::class,
        'custom_fields' => CustomFieldCast::class
    ];

    protected $appends = [];

    /**
     * @return false
     */
    public function getReadyAttribute(): bool
    {
        return $this->custom_fields->pick('ready') ?? false;
    }

    /**
     * @param             $builder
     * @param string|null $value
     */
    public function scopeFindByFqdn($builder, string $value = null)
    {
        $this->where('fqdn', $value);
    }

    /**
     * @return BelongsToMany
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, "hostname_modules", "hostname_id", "module_id");
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class , 'tenant_id');
    }

    public function operationCountries()
    {
        return $this->belongsToMany(Country::class, 'operation_countries')
            ->withPivot('active');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->host_id = (string)Uuid::uuid4();
        });
    }

//    public function receivedContracts()
//    {
//        return $this->hasMany(Contract::class, "receiver_id")
//            ->where('receiver_type', Hostname::class);
//    }
//
//    public function requestedContracts()
//    {
//        return $this->hasMany(Contract::class, "requester_id")
//            ->where('requester_type', Hostname::class);
//    }
//
//    public function getContractsAttribute()
//    {
//        $websiteUuid = $this->website()->first()?->uuid;
//
//        if (!$websiteUuid) {
//            return collect();
//        }
//
//        $contracts = Contract::where(function ($query) use ($websiteUuid) {
//            $query->where('receiver_id', $this->id)
//                ->where('receiver_connection', $websiteUuid)
//                ->where('receiver_type', self::class);
//        })->orWhere(function ($query) use ($websiteUuid) {
//            $query->where('requester_id', $this->id)
//                ->where('requester_connection', $websiteUuid)
//                ->where('requester_type', self::class);
//        })->get();
//
//        // Set the hostname context for each contract
//        $contracts->each(function ($contract) {
//            $contract->setHostnameContext($this);
//        });
//
//        return $contracts;
//    }
//
//    public function contracts()
//    {
//        return Contract::where(function ($query) {
//            $query->where('receiver_id', $this->id)
//                ->where('receiver_connection', $this->website->uuid)
//                ->where('receiver_type', Hostname::class);
//        })->orWhere(function ($query) {
//            $query->where('requester_id', $this->id)
//                ->where('requester_connection', $this->website->uuid)
//                ->where('requester_type', Hostname::class);
//        });
//    }

    public function getCompanyAttribute()
    {
        switchTenant($this->website->uuid);
        return Company::where('user_id', 1)->with('addresses')->first();
    }

    public function getCurrencyAttribute()
    {
        switchTenant($this->website->uuid);
        return Setting::where('key', 'currency')->first()->value;
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // Check if it's uploaded via admin to tenant media manager (assets disk)
        if (Storage::disk('assets')->exists($this->website->uuid . '/' . $this->logo)) {
            return Storage::disk('assets')->url($this->website->uuid . '/' . $this->logo);
        }

        // Check if it's an old format (uploaded from admin side to digitalocean)
        if (Storage::disk('digitalocean')->exists($this->logo)) {
            return Storage::disk('digitalocean')->url($this->logo);
        }

        // Check if it's an old admin upload pattern (suppliers/{uuid}.ext)
        if (Storage::disk('digitalocean')->exists('suppliers/' . $this->logo)) {
            return Storage::disk('digitalocean')->url('suppliers/' . $this->logo);
        }

        // If file doesn't exist anywhere, return null
        return null;
    }
}
