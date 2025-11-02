<?php

namespace App\Models;

use App\Enums\ContractType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_nr',
        'requester_id',
        'requester_type',
        'requester_connection',
        'receiver_id',
        'receiver_type',
        'receiver_connection',
        'st',
        'activated_at',
        'active',
        'callback',
        'webhook',
        'custom_fields',
        'start_at',
        'end_at',
        'blueprint_id',
        'type',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'custom_fields' => AsArrayObject::class,
        'start_at' => 'date',
        'end_at' => 'date',
        'type' => ContractType::class
    ];

    /**
     * @var string[]
     */
    protected $appends = ['has_handshake', 'am_requester', 'am_receiver'];

    protected $hostname_context;

    public function setHostnameContext($hostname)
    {
        $this->hostname_context = $hostname;
        return $this;
    }

    public function toArray()
    {
        $array = parent::toArray();

        // If we have a hostname context, use it for the flags
        if ($this->hostname_context) {
            $array['am_requester'] = $this->requester_id === $this->hostname_context->id &&
                $this->requester_type === get_class($this->hostname_context);
            $array['am_receiver'] = $this->receiver_id === $this->hostname_context->id &&
                $this->receiver_type === get_class($this->hostname_context);
            $array['has_handshake'] = $array['am_requester'] || $array['am_receiver'];
        }

        return $array;
    }

    /**
     * @return void
     */
    public static function boot(){
        parent::boot();

        static::creating(function ($contract) {
            // Find the latest contract record
            $latestContract = self::whereNotNull('contract_nr')
                ->orderBy('contract_nr', 'DESC')
                ->value('contract_nr');

            if ($latestContract) {
                // Extract the last number and increment it by 1
                $lastNumber = intval($latestContract);
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            } else {
                // If no contract exists, start with '000001'
                $newNumber = str_pad(1, 6, '0', STR_PAD_LEFT);
            }

            // Assign the new number to the model
            $contract->contract_nr = $newNumber;
        });


    }

    /**
     * Determine if the current tenant has a handshake with this contract.
     *
     * @return Attribute
     */
    protected function hasHandshake(): Attribute
    {
        return Attribute::make(
            get: function () {
                $currentTenant = hostname(); // Fetch current tenant using hostname()
                if (!$currentTenant) {
                    return false;
                }

                return $this->requester_id === $currentTenant->id &&
                    $this->requester_type === Hostname::class ||
                    $this->receiver_id === $currentTenant->id &&
                    $this->requester_type === Hostname::class;
            }
        );
    }

    /**
     * Determine if the current tenant is the requester.
     *
     * @return Attribute
     */
    protected function amRequester(): Attribute
    {
        return Attribute::make(
            get: function () {
                $currentTenant = hostname(); // Fetch current tenant using hostname()
                if (!$currentTenant) {
                    return false;
                }
                return $this->requester_id === $currentTenant->id &&
                    $this->requester_type === Hostname::class;
            }
        );
    }

    /**
     * Determine if the current tenant is the receiver.
     *
     * @return Attribute
     */
    protected function amReceiver(): Attribute
    {
        return Attribute::make(
            get: function () {
                $currentTenant = hostname(); // Fetch current tenant using hostname()
                if (!$currentTenant) {
                    return false;
                }
                return $this->receiver_id === $currentTenant->id &&
                    $this->receiver_type === Hostname::class;
            }
        );
    }

    // Relationship for the requester
    public function requester()
    {
        return $this->morphTo(null, 'requester_type', 'requester_id');
    }

    // Relationship for the receiver
    public function receiver()
    {
        return $this->morphTo(null, 'receiver_type', 'receiver_id');
    }

    public function scopeRequester(
        Builder $builder,
    )
    {

        $builder->where('requester_id', tenant()->id);
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'requester_id');
    }

    /**
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Hostname::class, 'receiver_id');
    }


    /**
     * @return BelongsTo
     */
//    public function receiver(): BelongsTo
//    {
//        return $this->belongsTo(Hostname::class, 'receiver_hostname_id');
//    }

}
