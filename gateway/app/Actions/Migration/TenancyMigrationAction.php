<?php


namespace App\Actions\Migration;


class TenancyMigrationAction
{
    /**
     * @param $path
     */
    public function register($path)
    {
        $paths = config('tenancy.db.tenant-migrations-path');
        $paths = collect($paths)->push($path)->toArray();
        config()->set('tenancy.db.tenant-migrations-path', $paths);
    }
}
