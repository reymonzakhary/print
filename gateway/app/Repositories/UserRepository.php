<?php


namespace App\Repositories;

use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use App\Enums\MemberType;
use App\Events\Tenant\User\TenantCreateUserEvent;
use App\Events\Tenant\User\TenantDeleteUserEvent;
use App\Models\Tenant\Company;
use App\Models\Tenant\Context;
use App\Models\Tenant\Role;
use App\Models\Tenant\Team;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Repository
 * @package App\Repositories
 */
class UserRepository extends InitModelAbstract implements RepositoryEloquentInterface
{

    /**
     * default access
     * @var string
     */
    protected string $ctx = 'mgr'; // mgr;

    /**
     * @inheritDoc
     */
    public function show(string|int $id, bool $member = false): ?Model
    {
        if ($user = $this->model->where('id', (int) $id)->with(
            'profile', 'contexts', 'roles', 'companies', 'teams', 'userTeams', 'addresses.country'
        )
            ->whereHas('contexts', fn ($q) =>  $q->where([
                ['member', '=', $member]
            ]))
            ->first()
        ) {
            return $user;
        }
        return null;
    }

    /**
     * @param int $per_page
     * @param bool $member
     * @param array $scopes
     * @return LengthAwarePaginator
     */
    public function all(int $per_page = 10, bool $member = false, array $scopes = [])
    {
        return $this->model
            ->with(
                'contexts', 'roles', 'companies', 'teams', 'userTeams', 'profile'
            )
            ->withScopes($scopes)
            ->whereHas('contexts', fn ($q) =>  $q->where([
                ['member', '=', $member]
            ]))
            ->paginate($per_page);
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes, $send = true): Model
    {
        $user = $this->model->create(collect($attributes)->only([
            'email',
            'password',
            'type',
        ])->toArray());


        $user->contexts()->sync([
            $attributes['ctx_id'] => [
                'member' => $attributes['member']
            ]
        ]);

        $user->profile()->create(collect($attributes)->only([
            'first_name', 'last_name','gender', 'dob', 'bio', 'avatar'
        ])->toArray());

        if ($attributes['type'] === MemberType::BUSINESS->value && optional($attributes)['company']) {
            $user->companies()->attach($attributes['company']);
        }

        if (isset($attributes['roles']) && count($attributes['roles']) > 0) {
            $user->addRoles($attributes['roles']);
        }

        if (isset($attributes['teams'])) {
            collect($attributes['teams'])->each(
                fn ($team) => $user->userTeams()->syncWithoutDetaching([$team['id'] => [
                    'admin' => (bool)optional($team)['admin'],
                    'authorizer' => (bool)optional($team)['authorizer']
                ]])
            );
        }

        if($send) {
            $user->sendApiEmailVerificationNotification(tenant()->uuid, $attributes['generated_password']);
        }
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $attributes, bool $member = false): bool
    {
        if ($user = $this->model->whereHas('contexts', function ($q)  use ($member) {
            $q->where('member', $member);
        })->where('id', $id)->first()) {

            if (isset($attributes['ctx_id']) && count($attributes['ctx_id'])) {
                $user->contexts()->detach();
                collect($attributes['ctx_id'])
                    ->each(
                        fn ($ctx) =>
                        $user->contexts()->syncWithoutDetaching([
                            $ctx => ['member' => $attributes['member']]
                        ])
                    );
                unset($attributes['ctx_id']);
            }

            if ($attributes['type'] === MemberType::BUSINESS->value && optional($attributes)['company']) {
                $user->companies()->sync($attributes['company']);
            }

            if (isset($attributes['roles'])) {
                $user->syncRoles($attributes['roles']);
            }

            if (isset($attributes['teams'])) {
                $user->userTeams()->detach();
                collect($attributes['teams'])->each(
                    fn ($team) =>
                    $user->userTeams()->syncWithoutDetaching([$team['id'] => [
                        'admin' => (bool)optional($team)['admin'],
                        'authorizer' => (bool)optional($team)['authorizer']
                    ]])
                );
            }

            $user->profile()->update(collect($attributes)->only([
                'first_name', 'last_name','gender', 'dob', 'bio', 'avatar'
            ])->toArray());

            return $user->update(collect($attributes)->only([
                'email', 'type',
            ])->toArray());
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id, string $ctx = 'web'): bool
    {
        $user = $this->model
            ->whereHas('contexts', function ($q) use ($ctx) {
                $q->where('name', $ctx);
            })
            ->where('id', (int) $id)
            ->first();
        if ($user && !$user->isOwner() && $user->delete()) {
            event(new TenantDeleteUserEvent($id));
            return true;
        }
        return false;
    }
}
