<?php


namespace App\Scoping;


use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Scoper
{
    /**
     * Scoper constructor.
     * @param Request $request
     */
    public function __construct(
        protected Request $request
    )
    {
    }

    /**
     * @param Builder $builder
     * @param array   $scopes
     * @return Builder
     */
    public function apply(
        Builder $builder,
        array   $scopes
    )
    {
        foreach ($scopes as $k => $scope) {
            if (!$scope instanceof Scope) continue;
            $scope->apply($builder, $this->request->get($k));
        }

        return $builder;
    }
}
