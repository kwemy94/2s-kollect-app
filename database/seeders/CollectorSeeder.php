<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Collector;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Auth\User;

class CollectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleCollector = Role::where('name', 'collector')->first();
        $lastCreatedUser = User::orderBy('id', 'desc')->first();

        // Droits collecteur
        $perm = Collector::create([
            'name' => 'collector test',
            'description' => 4,
        ]);

        $perm->roles()->attach([$roleCollector->id, $roleAdmin->id]);
    }
}
