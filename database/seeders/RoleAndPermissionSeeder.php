<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1️⃣ Clear cached permissions (important!)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2️⃣ Define all permissions (SOURCE OF TRUTH)
        $permissions = [
            'manage settings',
            'manage modules',
            'manage roles',
            'manage users',
            'view modules',
            'view users',
            'view roles',
        ];

        // 3️⃣ Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // 4️⃣ Create roles if they don't exist
        $superAdmin = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $hr = Role::firstOrCreate([
            'name' => 'HR',
            'guard_name' => 'web',
        ]);

        $registrar = Role::firstOrCreate([
            'name' => 'Registrar',
            'guard_name' => 'web',
        ]);

        $employee = Role::firstOrCreate([
            'name' => 'Employee',
            'guard_name' => 'web',
        ]);

        // 5️⃣ Assign permissions to roles
        $superAdmin->syncPermissions($permissions); // Full access
        $admin->syncPermissions([
            'manage modules',
            'manage users',
            'view modules',
            'view users',
        ]); // Admin limited
        $hr->syncPermissions([
            'manage users',
            'view users',
        ]); // HR
        $registrar->syncPermissions([
            'view modules',
            'manage users',
        ]); // Registrar
        $employee->syncPermissions([
            'view modules',
            'view users',
        ]); // Employee can only view

        // 6️⃣ Default users
        $defaultUsers = [
            [
                'name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@htpn',
                'password' => bcrypt('pass123'),
                'role' => $superAdmin,
            ],
            [
                'name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@htpn',
                'password' => bcrypt('admin123'),
                'role' => $admin,
            ],
            [
                'name' => 'HR',
                'last_name' => 'Manager',
                'email' => 'hr@htpn',
                'password' => bcrypt('hr123'),
                'role' => $hr,
            ],
            [
                'name' => 'Registrar',
                'last_name' => 'Office',
                'email' => 'registrar@htpn',
                'password' => bcrypt('reg123'),
                'role' => $registrar,
            ],
            [
                'name' => 'Employee',
                'last_name' => 'Staff',
                'email' => 'employee@htpn',
                'password' => bcrypt('emp123'),
                'role' => $employee,
            ],
        ];

        // 7️⃣ Create users and assign roles
        foreach ($defaultUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'last_name' => $data['last_name'],
                    'password' => $data['password'],
                ]
            );

            if (! $user->hasRole($data['role']->name)) {
                $user->assignRole($data['role']);
            }
        }

        // ✅ Feedback in console
        $this->command->info('Roles, permissions, and default users have been created successfully!');
    }
}
