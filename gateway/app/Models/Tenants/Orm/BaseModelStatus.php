<?php

namespace App\Models\Tenants\Orm;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;

class BaseModelStatus extends EloquentModel
{
    /**
     * Fetch all records.
     *
     * @param array $columns The columns to include in the result
     * @return Collection The merged and unique collection of records
     */
    public static function all(
        $columns = ['*']
    )
    {
        // Fetch all records
        return Status::getAllAsModel()->merge(parent::all($columns))->unique('code');
    }

    /**
     * Get specific models from the database and merge enum statuses.
     *
     * @param array $columns
     * @return Collection The merged and unique collection of records
     */
    public static function get(
        array $columns = ['*']
    )
    {
        // Fetch records
        return Status::getAllAsModel()->merge(parent::all($columns))->unique('code');
    }
}
