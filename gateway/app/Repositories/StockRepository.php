<?php


namespace App\Repositories;


use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Database\Eloquent\Model;

class StockRepository extends InitModelAbstract implements RepositoryEloquentInterface
{
    /**
     * @inheritDoc
     */
    public function show(int $id): ?Model
    {
        return $this->model->where('id', $id)->first();
    }

    /**
     * @inheritDoc
     */
    public function all(int $per_page = 10)
    {
        return $this->model->paginate($per_page);
    }

    /**
     * @inheritDoc
     */
    public function firstOrCreate(array $attributes): Model
    {
        return $this->model->firstOrCreate($attributes, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes): Model
    {
        return auth()->user()->stocks()->create($attributes);

    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $attributes): bool
    {
        if ($stock = $this->show($id)) {
            return $stock->update(
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
        $stock = $this->model->where('id', (int)$id)->first();
        if ($stock) {
            if ($stock->delete()) {
                return true;
            }
        }
        return false;
    }
}
