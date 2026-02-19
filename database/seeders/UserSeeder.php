<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * 3 admin/HR users + 15 faculty users = 18 total
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // ─── Admin / HR accounts ───────────────────────────────────────────
            $admins = [
                [
                    'username'          => 'super_admin',
                    'email'             => 'superadmin@university.edu',
                    'password'          => Hash::make('password'),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ],
                [
                    'username'          => 'admin',
                    'email'             => 'admin@university.edu',
                    'password'          => Hash::make('password'),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ],
                [
                    'username'          => 'hr_staff',
                    'email'             => 'hr@university.edu',
                    'password'          => Hash::make('password'),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ],
            ];

            $adminRoles = ['super_admin', 'admin', 'hr_staff'];

            foreach ($admins as $index => $data) {
                $user = User::firstOrCreate(['email' => $data['email']], $data);
                $role = Role::where('name', $adminRoles[$index])->where('guard_name', 'admin')->firstOrFail();
                $user->syncRoles([$role]);
            }

            // ─── Faculty user accounts ─────────────────────────────────────────
            // 15 faculty members: 3 per department (BSCS, BSIT, BSBA, BSED, BSCE)
            $facultyUsers = [
                // BSCS (dept index 1)
                ['username' => 'j.santos',       'email' => 'juan.santos@university.edu'],
                ['username' => 'm.cruz',          'email' => 'maria.cruz@university.edu'],
                ['username' => 'r.reyes',         'email' => 'roberto.reyes@university.edu'],
                // BSIT (dept index 2)
                ['username' => 'a.delacruz',      'email' => 'ana.delacruz@university.edu'],
                ['username' => 'c.mendoza',       'email' => 'carlos.mendoza@university.edu'],
                ['username' => 'e.ramos',         'email' => 'elena.ramos@university.edu'],
                // BSBA (dept index 3)
                ['username' => 'm.torres',        'email' => 'miguel.torres@university.edu'],
                ['username' => 's.villanueva',    'email' => 'sofia.villanueva@university.edu'],
                ['username' => 'a.garcia',        'email' => 'antonio.garcia@university.edu'],
                // BSED (dept index 4)
                ['username' => 'm.bautista',      'email' => 'marisol.bautista@university.edu'],
                ['username' => 'r.fernandez',     'email' => 'ricardo.fernandez@university.edu'],
                ['username' => 'l.aquino',        'email' => 'lourdes.aquino@university.edu'],
                // BSCE (dept index 5)
                ['username' => 'e.navarro',       'email' => 'eduardo.navarro@university.edu'],
                ['username' => 'c.morales',       'email' => 'cristina.morales@university.edu'],
                ['username' => 'f.castro',        'email' => 'fernando.castro@university.edu'],
            ];

            foreach ($facultyUsers as $data) {
                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'username'          => $data['username'],
                        'email'             => $data['email'],
                        'password'          => Hash::make('password'),
                        'is_active'         => true,
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles([Role::where('name', 'faculty')->where('guard_name', 'web')->firstOrFail()]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
