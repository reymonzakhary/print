<?php


namespace App\Repositories;


use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProfileRepository extends InitModelAbstract implements RepositoryEloquentInterface
{
    /**
     * @inheritDoc
     */
    public function show(int $id): ?Model
    {
        if ($user = $this->model->where('id', $id)->first()) {
            return $user->profile;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function all(int $per_page = 10)
    {
        // TODO: Implement all() method.
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes): Model
    {
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $attributes): bool
    {
        if (isset($attributes['custom_field'])) {
            $attributes['custom_field'] = json_encode($attributes['custom_field'], JSON_THROW_ON_ERROR, 512);
        }


        if (isset($attributes['dob'])) {
            $attributes['dob'] = Carbon::parse($attributes['dob']);
        }

        if ($user = $this->model->where('id', $id)->first()) {
            if (isset($attributes['ctx_id'])) {
                $user->contexts()->attach((int)$attributes['ctx_id']);
            }

            return $user->profile()->update(
                $attributes
            );
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
    }
}
