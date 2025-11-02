<?php

namespace App\Foundation\ContractManager\Traits;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @method morphMany(string $class, string $string)
 */
trait HasContract
{
    /**
     * Get contracts where this model is the requester
     */
    public function requestedContracts(): MorphMany
    {
        return $this->morphMany(Contract::class, 'requester');
    }

    /**
     * Get contracts where this model is the receiver
     */
    public function receivedContracts(): MorphMany
    {
        return $this->morphMany(Contract::class, 'receiver');
    }

    /**
     * @return BelongsTo
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function contracts():Builder
    {
        $connection = $this->website()->first()?->uuid;
        return Contract::where(function ($query) use ($connection) {
            $query->where(function ($q) use ($connection) {
                $q->where('requester_type', get_class($this))
                    ->where('requester_id', $this->id);
                if ($connection) {
                    $q->where('requester_connection', $connection);
                }
            })->orWhere(function ($q) use ($connection) {
                $q->where('receiver_type', get_class($this))
                    ->where('receiver_id', $this->id);
                if ($connection) {
                    $q->where('receiver_connection', $connection);
                }
            });
        });
    }

    /**
     * Get all contracts (both requested and received)
     */
    public function allContracts(): Collection
    {
        return $this->contracts()->get();
    }

    /**
     * Get active contracts only
     */
    public function activeContracts(): Collection
    {
        return $this->allContracts()->where('active', true);
    }

    /**
     * Check if this model has a contract with another model
     */
    public function hasContractWith($model): bool
    {
        return $this->allContracts()
            ->where(function ($contract) use ($model) {
                return ($contract->receiver_type === get_class($model) && $contract->receiver_id === $model->id) ||
                    ($contract->requester_type === get_class($model) && $contract->requester_id === $model->id);
            })
            ->isNotEmpty();
    }

    /**
     * Get contracts with a specific model
     */
    public function contractsWith($model): Collection
    {
        return $this->allContracts()
            ->filter(function ($contract) use ($model) {
                return ($contract->receiver_type === get_class($model) && $contract->receiver_id === $model->id) ||
                    ($contract->requester_type === get_class($model) && $contract->requester_id === $model->id);
            });
    }
}
