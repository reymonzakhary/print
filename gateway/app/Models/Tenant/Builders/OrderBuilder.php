<?php

namespace App\Models\Tenant\Builders;

use Illuminate\Database\Eloquent\Builder;

class OrderBuilder extends VirtualColumnBuilder
{
    /**
     * Determines whether the current user is the owner or has the necessary permissions.
     *
     * @return OrderBuilder|Builder|static The current instance of the OrderBuilder or Builder class.
     */
    public function whereOwnerOrAllowed(): OrderBuilder|Builder|static
    {
        if (auth()->user()->isOwner() || auth()->user()->contexts()->where('member', false)->exists()) {
            return $this;
        }
        return $this->where(function ($query) {
            $query->where('orders.user_id', auth()->user()->id)
                  ->orWhereIn(
                      'orders.team_id',
                      auth()->user()->userTeams()->where('user_teams.admin', true)->pluck('id')->toArray()
                  );
        });
    }
}
