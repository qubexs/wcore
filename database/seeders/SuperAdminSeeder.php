<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin role if not exists
        $role = Role::firstOrCreate(['name' => 'superadmin']);

        // Create superadmin user
        $user = User::firstOrCreate(
            ['email' => 'superadmin@htpn'], // unique identifier
            [
                'name' => 'Super',
                'last_name' => 'Admin',
                'password' => bcrypt('pass123'),
            ]
        );

        // Assign role
        $user->assignRole($role);
    }
}
