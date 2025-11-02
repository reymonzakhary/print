<?php

namespace App\Models;

use App\Exceptions\DomainCannotBeChangedException;
use App\Exceptions\SubdomainReservedException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    use HasFactory;

    protected $casts = [
        'is_primary' => 'bool',
        'is_fallback' => 'bool',
        'configure' => 'array', // If this is JSON data
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
}
