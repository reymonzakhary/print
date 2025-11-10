<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Addressable extends MorphPivot
{
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'identifier', 'address_id', 'addressable_type',
        'type', 'full_name', 'company_name', 'tax_nr', 'phone_number', 'default', 'team_address', 'team_id', 'team_name'
    ];

    protected $hidden = ['identifier', 'address_id'];


    /**
     * @return MorphTo
     */
    public function addressable()
    {
        return $this->morphTo();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($address) {
            if ($address->default) {
                $address
                    ->where('identifier', $address->identifier)
                    ->update([
                        'default' => false
                    ]);
            }
        });

        static::updating(function ($address) {
            if ($address->default) {
                $address
                    ->where('identifier', $address->identifier)
                    ->update([
                        'default' => false
                    ]);
            }
        });
        /**
         * Update and Create type
         */
        static::creating(function ($address) {
            if ($address->type === 'invoice') {
                $address
                    ->where('identifier', $address->identifier)
                    ->where('type', 'invoice')
                    ->update([
                        'type' => Null
                    ]);
            }
        });

        static::updating(function ($address) {
            if ($address->type === 'invoice') {
                $address
                    ->where('identifier', $address->identifier)
                    ->where('type', 'invoice')
                    ->update([
                        'type' => Null
                    ]);
            }
        });
        /**
         * when delivery updated or created
         */
        static::creating(function ($address) {
            if ($address->type === 'delivery') {
                $address
                    ->where('identifier', $address->identifier)
                    ->where('type', 'delivery')
                    ->update([
                        'type' => Null
                    ]);
            }
        });

        static::updating(function ($address) {
            if ($address->type === 'delivery') {
                $address
                    ->where('identifier', $address->identifier)
                    ->where('type', 'delivery')
                    ->update([
                        'type' => Null
                    ]);
            }
        });
    }
}
