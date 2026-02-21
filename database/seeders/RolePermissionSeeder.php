<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
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

            // ── Create all admin-guard permissions ─────────────────────────
            foreach (PermissionEnum::adminPermissions() as $permission) {
                Permission::firstOrCreate(['name' => $permission->value, 'guard_name' => 'admin']);
            }

            // ── Create all web-guard permissions ───────────────────────────
            foreach (PermissionEnum::webPermissions() as $permission) {
                Permission::firstOrCreate(['name' => $permission->value, 'guard_name' => 'web']);
            }

            // ── Create roles and sync permissions ──────────────────────────
            foreach (RoleEnum::cases() as $roleEnum) {
                $role = Role::firstOrCreate([
                    'name'       => $roleEnum->value,
                    'guard_name' => $roleEnum->guard(),
                ]);

                $role->syncPermissions(
                    collect($roleEnum->permissions())
                        ->map(fn(PermissionEnum $p) => Permission::where('name', $p->value)
                            ->where('guard_name', $roleEnum->guard())
                            ->first())
                        ->filter()
                        ->all()
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
