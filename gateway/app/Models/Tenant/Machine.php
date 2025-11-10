<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Orm\ExternalModel;
use App\Services\Machines\MachinesService;

class Machine extends  ExternalModel
{

    public string $service = MachinesService::class;


    public function toArray()
    {
        // TODO: Implement toArray() method.
    }

    public function escapeWhenCastingToString($escape = true)
    {
        // TODO: Implement escapeWhenCastingToString() method.
    }

    public function offsetExists(mixed $offset): bool
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet(mixed $offset): mixed
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset(mixed $offset): void
    {
        // TODO: Implement offsetUnset() method.
    }

    public function broadcastChannelRoute()
    {
        // TODO: Implement broadcastChannelRoute() method.
    }

    public function broadcastChannel()
    {
        // TODO: Implement broadcastChannel() method.
    }

    public function toJson($options = 0)
    {
        // TODO: Implement toJson() method.
    }

    public function getQueueableId()
    {
        // TODO: Implement getQueueableId() method.
    }

    public function getQueueableRelations()
    {
        // TODO: Implement getQueueableRelations() method.
    }

    public function getQueueableConnection()
    {
        // TODO: Implement getQueueableConnection() method.
    }

    public function getRouteKey()
    {
        // TODO: Implement getRouteKey() method.
    }

    public function getRouteKeyName()
    {
        // TODO: Implement getRouteKeyName() method.
    }

    public function resolveRouteBinding($value, $field = null)
    {
        // TODO: Implement resolveRouteBinding() method.
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }

    public function jsonSerialize(): mixed
    {
        // TODO: Implement jsonSerialize() method.
    }
}
