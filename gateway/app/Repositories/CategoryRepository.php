<?php


namespace App\Repositories;


use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository extends InitModelAbstract implements RepositoryEloquentInterface
{

    /**
     * @inheritDoc
     */
    public function show($id): ?Model
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
        return $this->model->paginate($per_page);
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
    public function update($id, array $attributes): bool
    {
        if ($category = $this->model->where('id', $id)->first()) {
            return $category->update(
                $attributes
            );
        }
        return false;
    }


    /**
     * @inheritDoc
     */
    public function delete($id): bool
    {
        $category = $this->model->where('id', $id)->first();
        if ($category) {
            if ($category->delete()) {
                return true;
            }
        }
        return false;
    }
}
