<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // ── Admin-guard permissions (super_admin · admin · hr_staff) ───────
            $adminPermissions = [
                // Dashboard
                'view dashboard',

                // Faculty
                'view faculty',
                'create faculty',
                'edit faculty',
                'delete faculty',

                // Departments
                'view departments',
                'create departments',
                'edit departments',
                'delete departments',

                // Schedules
                'view schedules',
                'create schedules',
                'edit schedules',
                'delete schedules',

                // Attendance
                'view attendance',
                'create attendance',
                'edit attendance',
                'delete attendance',

                // DTR
                'generate dtr',

                // Leaves
                'view leaves',
                'create leaves',
                'edit leaves',
                'delete leaves',

                // Reports
                'view reports',

                // Settings
                'view settings',
                'create settings',
                'edit settings',
                'delete settings',

                // Users
                'view users',
                'create users',
                'edit users',
                'delete users',

                // Biometric
                'import biometric logs',

                // Holidays
                'view holidays',
                'create holidays',
                'edit holidays',
                'delete holidays',

                // Requests
                'view requests',
                'approve requests',
                'reject requests',

                // Roles & Permissions Management
                'manage roles',
                'manage permissions',

                // Logs
                'view logs',

                // System Settings
                'view system settings',
                'edit system settings',
            ];

            foreach ($adminPermissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
            }

            // ── Web-guard permissions (faculty) ────────────────────────────────
            $webPermissions = [
                'view dashboard',
                'view attendance',
                'generate dtr',
                'view own requests',
                'create own requests',
                'edit own requests',
                'delete own requests',
            ];

            foreach ($webPermissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            }

            // ── Admin-guard roles ──────────────────────────────────────────────
            $adminRolePermissions = [
                'super_admin' => $adminPermissions,
                'admin'       => $adminPermissions,
                'hr_staff'    => [
                    'view dashboard',
                    'view faculty',
                    'create faculty',
                    'edit faculty',
                    'delete faculty',
                    'view attendance',
                    'create attendance',
                    'edit attendance',
                    'delete attendance',
                    'generate dtr',
                    'view leaves',
                    'create leaves',
                    'edit leaves',
                    'delete leaves',
                    'view reports',
                    'import biometric logs',
                ],
            ];

            foreach ($adminRolePermissions as $roleName => $perms) {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin']);
                $role->syncPermissions(
                    collect($perms)->map(fn($p) => Permission::where('name', $p)->where('guard_name', 'admin')->first())->filter()->all()
                );
            }

            // ── Web-guard roles ────────────────────────────────────────────────
            $facultyRole = Role::firstOrCreate(['name' => 'faculty', 'guard_name' => 'web']);
            $facultyRole->syncPermissions(
                collect($webPermissions)->map(fn($p) => Permission::where('name', $p)->where('guard_name', 'web')->first())->filter()->all()
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
