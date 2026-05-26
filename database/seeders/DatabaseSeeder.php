<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1️⃣ Clear cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2️⃣ Define permissions
        $permissions = [
            'manage settings',
            'manage modules',
            'manage roles',
            'manage users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 3️⃣ Define roles
        $superAdmin = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $hr         = Role::firstOrCreate(['name' => 'HR', 'guard_name' => 'web']);
        $registrar  = Role::firstOrCreate(['name' => 'Registrar', 'guard_name' => 'web']);
        $employee   = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);

        // 4️⃣ Assign permissions to roles
        $superAdmin->syncPermissions($permissions); // all permissions
        $admin->syncPermissions(['manage modules', 'manage users']);
        $hr->syncPermissions([]); // customize later
        $registrar->syncPermissions([]); // customize later
        $employee->syncPermissions([]); // customize later

        // 5️⃣ Define default users
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

        // 6️⃣ Create users and assign roles
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

        // ✅ Feedback
        $this->command->info('Default roles, permissions, and users have been seeded!');
    }
}
