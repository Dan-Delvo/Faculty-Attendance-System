<?php

namespace Database\Seeders;

use App\Services\FlssBackendClient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Creates admin accounts plus faculty users sourced from the external API.
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
                [
                    'username'          => 'head_academic_program',
                    'email'             => 'head.academic@university.edu',
                    'password'          => Hash::make('password'),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ],
            ];

            $adminRoles = ['super_admin', 'admin', 'hr_staff', 'head_academic_program'];

            foreach ($admins as $index => $data) {
                $user = User::firstOrCreate(['email' => $data['email']], $data);
                $role = Role::where('name', $adminRoles[$index])->where('guard_name', 'admin')->firstOrFail();
                $user->syncRoles([$role]);
            }

            // ─── Faculty user accounts from external API ──────────────────────
            $facultyUsers = collect($this->fetchFacultySchedulesFromApi())
                ->map(function (array $item): array {
                    $email = strtolower(trim((string) ($item['faculty_email'] ?? '')));
                    $usernameBase = Str::of((string) Str::before($email, '@'))
                        ->lower()
                        ->replaceMatches('/[^a-z0-9._-]/', '')
                        ->limit(50, '')
                        ->toString();

                    return [
                        'username' => $usernameBase !== '' ? $usernameBase : 'faculty_' . (int) ($item['faculty_id'] ?? 0),
                        'email'    => $email,
                    ];
                })
                ->filter(fn(array $u) => $u['email'] !== '')
                ->values();

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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchFacultySchedulesFromApi(): array
    {
        $client = app(FlssBackendClient::class);
        $response = $client->getFacultySchedules(['per_page' => 500]);

        if (! $response->successful()) {
            throw new \RuntimeException('External schedules API request failed while seeding users. HTTP ' . $response->status());
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new \RuntimeException('External schedules API returned an invalid JSON payload while seeding users.');
        }

        $records = data_get($payload, 'parttime_faculty_schedules');

        if (! is_array($records)) {
            $records = data_get($payload, 'data.parttime_faculty_schedules');
        }

        if (! is_array($records)) {
            $records = data_get($payload, 'data');
        }

        if (! is_array($records)) {
            $records = [];
        }

        return array_values(array_filter($records, fn ($record) => is_array($record)));
    }
}
