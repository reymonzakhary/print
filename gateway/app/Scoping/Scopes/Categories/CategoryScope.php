<?php


namespace App\Scoping\Scopes\Categories;


use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class CategoryScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value  && $value !== 'undefined') {
            $builder->where('category_id', $value)
                ->whereIn('category_id', auth()->user()
                    ->userTeams
                    ->map(fn ($team) => $team->category()->pluck('row_id'))
                    ->flatten(1)->toArray()
                );
        }
    }
}
