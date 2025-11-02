<?php

declare(strict_types=1);

namespace App\Models\Tenants\Builders;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Builder;

final class ItemBuilder extends Builder
{
    /**
     * @param Status $status
     *
     * @return ItemBuilder
     */
    public function whereStatus(Status $status): ItemBuilder
    {
        return $this->where('st', $status->value);
    }

    /**
     * @param Status $status
     *
     * @return ItemBuilder
     */
    public function whereStatusNot(Status $status): ItemBuilder
    {
        return $this->whereNot('st', $status->value);
    }

    /**
     * Specifies the "whereStatusIsDraft" method.
     *
     * @return ItemBuilder The updated query builder instance.
     */
    public function whereStatusIsDraft(): ItemBuilder
    {
        return $this->whereStatus(Status::DRAFT);
    }

    /**
     * Specifies the "whereStatusIsNotCancelled" method.
     *
     * @return ItemBuilder The updated query builder instance.
     */
    public function whereStatusIsNotCancelled(): ItemBuilder
    {
        return $this->whereStatusNot(Status::CANCELED);
    }
}
