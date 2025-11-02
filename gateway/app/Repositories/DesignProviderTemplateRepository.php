<?php


namespace App\Repositories;

use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class Repostitory
 * @package App\Repositories
 */
class DesignProviderTemplateRepository extends InitModelAbstract implements RepositoryEloquentInterface
{

    /**
     * default access
     * @var array
     */
    protected string $ctx = 'mgr'; // mgr;

    /**
     * @inheritDoc
     */
    public function show(int $id): ?Model
    {
        if ($status = $this->model->where('id', $id)->first()) {
            return $status;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function all(int $per_page = 10)
    {
        return $this->model->get();
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
        if ($status = $this->model->where('id', $id)->first()) {
            return $status->update(
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
        $status = $this->model->where('id', (int)$id)->first();
        $status->removeMedia('design-provider-templates');
        Storage::disk('tenant')->deleteDirectory("Providers/{$status->designProvider->name}/templates/{$status->name}");
        if ($status) {
            if ($status->delete()) {
                return true;
            }
        }
        return false;
    }
}
