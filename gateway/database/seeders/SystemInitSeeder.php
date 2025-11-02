<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SystemInitSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $user = User::firstOrCreate([
            'email' => 'reymon@prindustry.nl',
        ], [
            'username' => 'prindustry',
            'email' => 'reymon@prindustry.nl',
            'password' => 'prindustry'
        ]);

        $user->profile()->firstOrCreate([
            'first_name' => 'Reymon',
            'last_name' => 'zakhary'
        ], [
            'first_name' => 'Reymon',
            'last_name' => 'zakhary'
        ]);

//        $user->roles()->sync([1]);
        // @ this is temporary until we fixed the manager
        User::all()->each(function ($user) {
           $user->roles()->sync([1]);
        });


    }
}
