<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'Admin')->first();

        if (!Users::where('email', 'admin@travely.com')->exists()) {
            Users::factory()->create([
                'userName' => 'admin',
                'email' => 'admin@travely.com',
                'is_admin' => true,
                'role_id' => $adminRole->role_id,
            ]);
        }
    }
}
