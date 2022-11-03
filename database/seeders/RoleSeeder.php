<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'client',
            'description' => 'utilisateur client',
        ]);
        Role::create([
            'name' => 'root',
            'description' => 'super admin',
        ]);
        Role::create([
            'name' => 'collector',
            'description' => 'utilisateur collecteur',
        ]);
    }
}
