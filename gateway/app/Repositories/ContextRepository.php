<?php


namespace App\Repositories;


use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Database\Eloquent\Model;

class ContextRepository extends InitModelAbstract implements RepositoryEloquentInterface
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
        return $this->model->with('addresses')->paginate($per_page);
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
        // ddd
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
    }
}
