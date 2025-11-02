<?php


namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface RepositoryEloquentInterface
 * @package App\Contracts
 */
interface RepositoryEloquentInterface
{
    /**
     * @param int    $id
     * @param string $ctx
     * @param bool   $member
     * @return Model|null
     */
    public function show(int $id): ?Model;

    /**
     * @param int    $per_page
     * @param string $ctx
     * @param bool   $member
     * @return Collection
     */
    public function all(int $per_page = 10);

    /**
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes): Model;

    /**
     * @param array $attributes
     * @param int   $id
     * @return mixed
     */
    public function update(int $id, array $attributes): bool;

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): bool;
}
