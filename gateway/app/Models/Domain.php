<?php

namespace App\Models;

use App\Casts\Hostname\CustomFieldCast;
use App\Exceptions\DomainCannotBeChangedException;
use App\Exceptions\SubdomainReservedException;
use App\Foundation\ContractManager\Traits\HasContract;
use App\Models\Tenants\Company;
use App\Models\Tenants\Setting;
use App\Models\Traits\CanBeScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    use HasFactory, CanBeScoped, HasContract;

    protected $fillable = [
        'domain',
        'tenant_id',
        'is_primary',
        'is_fallback',
        'configure',
        'supplier',
        'logo',
        'host_id',
        'custom_fields',
    ];

    protected $casts = [
        'is_primary' => 'bool',
        'is_fallback' => 'bool',
        'configure' => 'array',
        'custom_fields' => CustomFieldCast::class,
    ];

    /**
     *
     */
    public static function booted()
    {
        static::saving(function (self $model) {
            if (in_array($model->domain, config('saas.reserved_subdomains'))) {
                throw new SubdomainReservedException($model->domain);
            }
        });

        static::updating(function (self $model) {
            if ($model->getAttribute('domain') !== $model->getOriginal('domain')) {
                throw new DomainCannotBeChangedException;
            }
        });

        static::saved(function (self $model) {
            // There can only be one of these
            $uniqueKeys = ['is_primary', 'is_fallback'];

            foreach ($uniqueKeys as $key) {
                if ($model->$key) {
                    $model->tenant->domains()
                        ->where('id', '!=', $model->id)
                        ->update([$key => false]);
                }
            }
        });
    }

    /**
     * @param string $subdomain
     * @return string
     */
    public static function domainFromSubdomain(string $subdomain): string
    {
        return $subdomain . '.' . config('tenancy.central_domains')[0];
    }

    /**
     * @return $this
     */
    public function makePrimary(): self
    {
        $this->update([
            'is_primary' => true,
        ]);

        $this->tenant->setRelation('primary_domain', $this);

        return $this;
    }

    /**
     * @return $this
     */
    public function makeFallback(): self
    {
        $this->update([
            'is_fallback' => true,
        ]);

        $this->tenant->setRelation('fallback_domain', $this);

        return $this;
    }

    /**
     * @return bool
     */
    public function isSubdomain(): bool
    {
        return !Str::contains($this->domain, '.');
    }

    /**
     * Get the domain type.
     * Returns 'subdomain' or 'domain'.
     *
     * @return string
     */
    public function getTypeAttribute(): string
    {
        return $this->isSubdomain() ? 'subdomain' : 'domain';
    }

    /**
     * Get current domain for the active tenant
     *
     * @param $builder
     * @return mixed
     */
    public function scopeCurrent($builder): mixed
    {
        $tenant = tenant();
        if (!$tenant) {
            return null;
        }
        return $builder->where('tenant_id', $tenant->id)->where('is_primary', true)->first();
    }

    /**
     * Backward compatibility attribute for 'fqdn'
     */
    public function getFqdnAttribute()
    {
        return $this->domain;
    }

    /**
     * Backward compatibility attribute for 'primary'
     */
    public function getPrimaryAttribute()
    {
        return $this->is_primary;
    }

    /**
     * Setter for backward compatibility
     */
    public function setFqdnAttribute($value)
    {
        $this->attributes['domain'] = $value;
    }

    /**
     * Setter for backward compatibility
     */
    public function setPrimaryAttribute($value)
    {
        $this->attributes['is_primary'] = $value;
    }

    /**
     * Get ready status from custom fields
     */
    public function getReadyAttribute(): bool
    {
        return $this->custom_fields->pick('ready') ?? false;
    }

    /**
     * Find domain by fqdn
     *
     * @param             $builder
     * @param string|null $value
     */
    public function scopeFindByFqdn($builder, string $value = null)
    {
        return $builder->where('domain', $value);
    }

    /**
     * Modules relationship
     *
     * @return BelongsToMany
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, "hostname_modules", "hostname_id", "module_id");
    }

    /**
     * Delivery zones relationship
     */
    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class , 'tenant_id');
    }

    /**
     * Operation countries relationship
     */
    public function operationCountries()
    {
        return $this->belongsToMany(Country::class, 'operation_countries')
            ->withPivot('active');
    }

    /**
     * Relationship to website (tenant) for backward compatibility
     */
    public function website()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Get company for this domain
     */
    public function getCompanyAttribute()
    {
        switchTenant($this->website->id);
        return Company::where('user_id', 1)->with('addresses')->first();
    }

    /**
     * Get currency for this domain
     */
    public function getCurrencyAttribute()
    {
        switchTenant($this->website->id);
        return Setting::where('key', 'currency')->first()->value;
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // Check if it's a new format (updated from tenant side)
        if (Storage::disk('assets')->exists($this->website->id . '/' . $this->logo)) {
            return Storage::disk('assets')->url($this->website->id . '/' . $this->logo);
        }

        // Check if it's an old format (updated from admin side)
        if (Storage::disk('digitalocean')->exists($this->logo)) {
            return Storage::disk('digitalocean')->url($this->logo);
        }

        return null;
    }

    /**
     * Boot method to add UUID generation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->host_id) {
                $model->host_id = (string)Uuid::uuid4();
            }
        });
    }
}
