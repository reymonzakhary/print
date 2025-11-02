<?php

namespace Database\Seeders\Tenants;

use App\Models\Area;
use App\Models\Tenants\Npace;
use App\Models\Tenants\Permission;
use App\Models\Tenants\Role;
use App\Models\Tenants\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return  void
     */
    public function run()
    {
        $this->command->info('Truncating User, Role and Permission tables');
        $this->truncateLaratrustTables();

        $config = config('laratrust_seeder.roles_structure');
        $mapPermission = collect(config('laratrust_seeder.permissions_map'));
        $routePermission = collect(config('laratrust_seeder.permissions_by_route'));

        foreach ($config as $key => $permissionType) {

            // Create a new role
            $role = Role::firstOrCreate([
                'name' => $key,
                'display_name' => ucwords(str_replace('_', ' ', $key)),
                'description' => ucwords(str_replace('_', ' ', $key))
            ]);
            $permissions = [];

            $this->command->info('Creating Role ' . strtoupper($key));

            // Reading role permission permissionType
            foreach ($permissionType['permissions'] as $module => $value) {

                foreach (explode(',', $value) as $p => $perm) {

                    $permissionValue = $mapPermission->get($perm);
                    $namespaces = explode('_', $module);
                    $permissions[] = Permission::firstOrCreate([
                        'name' => str_replace('_', '-', $module) . '-' . $permissionValue,
                        'namespace' => $namespaces[0],
                        'area' => count($namespaces) > 1 ? $namespaces[1] : 'default',
                        'display_name' => $permissionValue,
                        'description' => ucfirst($permissionValue) . ' ' . ucfirst(str_replace(['-', '_'], ' ', $module)),
                    ])->id;

                    $namespace = Npace::firstOrCreate(['name' => $namespaces[0]]);
                    $namespace->update(['disabled' => false]);
                    $namespace = \App\Models\Npace::firstOrCreate(['name' => $namespaces[0]], ['name' => $namespaces[0]]);
                    \App\Models\Tenants\Area::firstOrCreate(['name' => count($namespaces) > 1 ? $namespaces[1] : 'default'], ['name' => count($namespaces) > 1 ? $namespaces[1] : 'default']);
                    $area = Area::firstOrCreate(['name' => count($namespaces) > 1 ? $namespaces[1] : 'default'], ['name' => count($namespaces) > 1 ? $namespaces[1] : 'default']);
                    $namespace->areas()->syncWithoutDetaching($area);

                    $this->command->info('Creating Permission to ' . $permissionValue . ' for ' . $module);
                }

            }
//            foreach ($permissionType['permissions_by_route'] as $module) {
//                foreach ($routePermission[$module] as $perm) {
//                    $permissionDetails = explode('.',$perm);
//                    $permissions[] = \App\Models\Tenants\Permission::firstOrCreate([
//                        'name' => $perm,
//                        'display_name' => ucfirst($permissionDetails[1]),
//                        'description' => ucfirst($permissionDetails[1]),
//                    ])->id;
//
//                    $this->command->info('Creating Permission to '.$perm.' for '. $module);
//                }
//            }

            // Attach all permissions to the role
            $role->permissions()->sync($permissions);

            if (Config::get('laratrust_seeder.create_users')) {
                $this->command->info("Creating '{$key}' user");
                // Create default user for each role
                $user = User::create([
                    'name' => ucwords(str_replace('_', ' ', $key)),
                    'email' => $key . '@app.com',
                    'password' => bcrypt('password')
                ]);
                $user->addRole($role);
            }

        }
    }

    /**
     * Truncates all the laratrust tables and the users table
     *
     * @return    void
     */
    public function truncateLaratrustTables()
    {
//        Schema::disableForeignKeyConstraints();
//        DB::table('permission_role')->truncate();
//        DB::table('permission_user')->truncate();
//        DB::table('role_user')->truncate();
//        if(Config::get('laratrust_seeder.truncate_tables')) {
//            \App\Models\Tenants\Role::truncate();
//            \App\Models\Tenants\Permission::truncate();
//        }
//        if(Config::get('laratrust_seeder.truncate_tables') && Config::get('laratrust_seeder.create_users')) {
//            \App\Models\Tenants\User::truncate();
//        }
//        Schema::enableForeignKeyConstraints();
    }
}
