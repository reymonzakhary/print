<?php


namespace App\Repositories;


use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Database\Eloquent\Model;

class OptionRepository extends InitModelAbstract implements RepositoryEloquentInterface
{
    /**
     * @inheritDoc
     */
    public function show(int $id): ?Model
    {
        return $this->model->with('parent')->where('id', $id)->first();
    }

    /**
     * @inheritDoc
     */
    public function all(int $per_page = 10)
    {
        return $this->model->with('parent')->paginate($per_page);
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
        return $this->model->create($attributes);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $attributes): bool
    {
        if ($option = $this->show($id)) {
            return $option->update(
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
        $option = $this->model->where('id', (int)$id)->first();
        if ($option) {
            if ($option->delete()) {
                return true;
            }
        }
        return false;
    }
}
