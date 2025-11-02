<?php


namespace App\Repositories;

use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Repository
 * @package App\Repositories
 */
class StatusRepository extends InitModelAbstract implements RepositoryEloquentInterface
{

    /**
     * @inheritDoc
     */
    final public function show(int $code): ?Model
    {
        if ($status = $this->model->where('code', $code)->first()) {
            return $status;
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
        //
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $attributes): bool
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        //
    }
}
