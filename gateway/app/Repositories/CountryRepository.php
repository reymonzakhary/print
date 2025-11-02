<?php


namespace App\Repositories;


use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Database\Eloquent\Model;

class CountryRepository extends InitModelAbstract implements RepositoryEloquentInterface
{

    /**
     * @inheritDoc
     */
    public function show(int $id): ?Model
    {
        if ($country = $this->model->where('id', $id)->first()) {
            return $country;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function all(int $per_page = 10)
    {
        return $this->model->all();
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
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
    }
}
