<?php

namespace App\Repositories;

use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Repostitory
 * @package App\Repositories
 */
final class LexiconRepository extends InitModelAbstract implements RepositoryEloquentInterface
{
    /**
     * @param int $per_page
     * @param string $template
     * @return LengthAwarePaginator|Collection
     */
    public function all(int $per_page = 10, string $template = '')
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($per_page);
    }

    /**
     * Retrieve the templates by a given namespace
     *
     * @param string $namespace
     * @param string|null $area
     * @param array $scopes
     *
     * @return mixed
     */
    public function template(
        string $namespace,
        string $area = null,
        array  $scopes = []
    ): mixed
    {
        return $this->model
            ->where('namespace', $namespace)
            ->where('area', $area)
            ->withScopes($scopes)
            ->get();
    }

    public function show(int $id): ?Model
    {
        if ($lexcon = $this->model->where('id', $id)->first()) {
            return $lexcon;
        }
        return null;
    }

    /**
     * @param array $attributes
     * @return Model
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
        $lexcon = $this->model->where('id', $id)->first();
        $lexcon->fill($attributes);
        return $lexcon->save();
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $lexcon = $this->model->where('id', (int)$id)->first();
        if ($lexcon) {
            if ($lexcon->delete()) {
                return true;
            }
        }
        return false;
    }
}
