<?php


namespace App\Repositories;


use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Database\Eloquent\Model;

class SettingRepository extends InitModelAbstract implements RepositoryEloquentInterface
{
    /**
     * @inheritDoc
     */
    public function show($key): ?Model
    {
        return $this->model->where('key', $key)->first();
    }

    /**
     * @inheritDoc
     */
    public function all(int $per_page = 10)
    {
        $setting = $this->model;
        if (request()->namespace)
            $setting = $setting->where('namespace', request()->namespace);
        if (request()->lexicon)
            $setting = $setting->where('lexicon', request()->lexicon);
        return $setting->paginate($per_page);
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
    public function update($slug, array $attributes): bool
    {
        if ($setting = $this->show($slug)) {
            return $setting->update(
                $attributes
            );
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        $setting = $this->model->where('key', (int)$key)->first();
        if ($setting) {
            if ($setting->delete()) {
                return true;
            }
        }
        return false;
    }
}
