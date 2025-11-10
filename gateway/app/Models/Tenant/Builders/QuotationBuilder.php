<?php

namespace App\Models\Tenant\Builders;

use Illuminate\Database\Eloquent\Builder;

class QuotationBuilder extends Builder
{

    /**
     * Specifies the "whereOwnerOrAllowed" method.
     *
     * @return OrderBuilder|Builder|static The updated query builder instance.
     */
    public function whereOwnerOrAllowed(): OrderBuilder|Builder|static
    {

        if (auth()->user()->isOwner() || auth()->user()->contexts()->where('member', false)->exists()) {
            return $this;
        }

        return $this->where('orders.user_id', auth()->user()->id)
            ->orWhereIn(
                'orders.team_id',
                auth()->user()->userTeams()->where('user_teams.admin', true)->pluck('id')->toArray()
            );
    }
}
