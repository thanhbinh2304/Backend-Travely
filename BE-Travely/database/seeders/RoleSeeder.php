<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::updateOrCreate(
            ['role_id' => 1],
            [
                'name' => 'Admin',
                'description' => 'Administrator role',
                'active' => 1,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]
        );

        Role::updateOrCreate(
            ['role_id' => 2],
            [
                'name' => 'User',
                'description' => 'Normal user role',
                'active' => 1,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]
        );
    }
}
