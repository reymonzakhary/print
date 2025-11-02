<?php

namespace App\Listeners\System;

use App\Events\System\ClientUpdatedEvent;
use App\Models\Tenants\Npace;
use App\Models\Tenants\Permission;
use App\Models\Tenants\Role;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class ClientListener
{

    public function handleUpdatedClient($event)
    {
        $website = $event->hostname->website;

        if(isset($event->hostname->configure['namespaces'][0][0])){
            $event->hostname->configure['namespaces'] = array_map(function ($namespace) {
                return array_shift($namespace);
            }, $event->hostname->configure['namespaces']);
            $event->hostname->update(['configure' => $event->hostname->configure]);
        }


        // filter namespaces and forget the core
        $namespaces = collect($event->hostname->configure->toArray()['namespaces'])->filter( fn ($item) => $item['namespace'] !== 'core' );

        switchSupplier($website->uuid);

        // disable namespaces not selected
        Npace::whereNotIn('name', $namespaces->pluck('namespace')->toArray())->update(['disabled' => true]);

        // enable selected namespaces
        Npace::whereIn('name', $namespaces->pluck('namespace')->toArray())->update(['disabled' => false]);

        // get permissions to enable
        $permissions = Permission::whereIn('namespace', $namespaces->pluck('namespace')->toArray())->get();

        $roles = Role::whereNotIn('name', array_keys(config('laratrust_seeder.roles_structure')))->get();

        // detach permissions from the roles
        $roles->each(fn ($role) => $role->syncPermissions($permissions));
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            ClientUpdatedEvent::class,
            [ClientListener::class, 'handleUpdatedClient']
        );
    }
}
