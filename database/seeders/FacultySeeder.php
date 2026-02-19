<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultySeeder extends Seeder
{
    /**
     * 15 faculty members — 3 per department.
     * biometric_id format: BIO001–BIO015
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Resolve department IDs by code
            $deptIds = Department::pluck('id', 'code');

            // Resolve user IDs by email
            $userIds = User::pluck('id', 'email');

            $faculty = [
                // ── BSCS ──────────────────────────────────────────────────────
                [
                    'email'           => 'juan.santos@university.edu',
                    'dept_code'       => 'BSCS',
                    'faculty_code'    => 'FAC-BSCS-001',
                    'biometric_id'    => 'BIO001',
                    'first_name'      => 'Juan',
                    'middle_name'     => 'M.',
                    'last_name'       => 'Santos',
                    'phone'           => '09171234001',
                    'employment_type' => 'regular',
                    'date_hired'      => '2018-06-01',
                ],
                [
                    'email'           => 'maria.cruz@university.edu',
                    'dept_code'       => 'BSCS',
                    'faculty_code'    => 'FAC-BSCS-002',
                    'biometric_id'    => 'BIO002',
                    'first_name'      => 'Maria',
                    'middle_name'     => 'L.',
                    'last_name'       => 'Cruz',
                    'phone'           => '09171234002',
                    'employment_type' => 'regular',
                    'date_hired'      => '2019-08-01',
                ],
                [
                    'email'           => 'roberto.reyes@university.edu',
                    'dept_code'       => 'BSCS',
                    'faculty_code'    => 'FAC-BSCS-003',
                    'biometric_id'    => 'BIO003',
                    'first_name'      => 'Roberto',
                    'middle_name'     => 'A.',
                    'last_name'       => 'Reyes',
                    'phone'           => '09171234003',
                    'employment_type' => 'part_time',
                    'date_hired'      => '2021-06-01',
                ],
                // ── BSIT ──────────────────────────────────────────────────────
                [
                    'email'           => 'ana.delacruz@university.edu',
                    'dept_code'       => 'BSIT',
                    'faculty_code'    => 'FAC-BSIT-001',
                    'biometric_id'    => 'BIO004',
                    'first_name'      => 'Ana',
                    'middle_name'     => 'R.',
                    'last_name'       => 'Dela Cruz',
                    'phone'           => '09181234004',
                    'employment_type' => 'regular',
                    'date_hired'      => '2017-06-01',
                ],
                [
                    'email'           => 'carlos.mendoza@university.edu',
                    'dept_code'       => 'BSIT',
                    'faculty_code'    => 'FAC-BSIT-002',
                    'biometric_id'    => 'BIO005',
                    'first_name'      => 'Carlos',
                    'middle_name'     => 'D.',
                    'last_name'       => 'Mendoza',
                    'phone'           => '09181234005',
                    'employment_type' => 'regular',
                    'date_hired'      => '2020-08-01',
                ],
                [
                    'email'           => 'elena.ramos@university.edu',
                    'dept_code'       => 'BSIT',
                    'faculty_code'    => 'FAC-BSIT-003',
                    'biometric_id'    => 'BIO006',
                    'first_name'      => 'Elena',
                    'middle_name'     => 'G.',
                    'last_name'       => 'Ramos',
                    'phone'           => '09181234006',
                    'employment_type' => 'part_time',
                    'date_hired'      => '2022-01-10',
                ],
                // ── BSBA ──────────────────────────────────────────────────────
                [
                    'email'           => 'miguel.torres@university.edu',
                    'dept_code'       => 'BSBA',
                    'faculty_code'    => 'FAC-BSBA-001',
                    'biometric_id'    => 'BIO007',
                    'first_name'      => 'Miguel',
                    'middle_name'     => 'P.',
                    'last_name'       => 'Torres',
                    'phone'           => '09191234007',
                    'employment_type' => 'regular',
                    'date_hired'      => '2016-06-01',
                ],
                [
                    'email'           => 'sofia.villanueva@university.edu',
                    'dept_code'       => 'BSBA',
                    'faculty_code'    => 'FAC-BSBA-002',
                    'biometric_id'    => 'BIO008',
                    'first_name'      => 'Sofia',
                    'middle_name'     => 'C.',
                    'last_name'       => 'Villanueva',
                    'phone'           => '09191234008',
                    'employment_type' => 'regular',
                    'date_hired'      => '2019-06-01',
                ],
                [
                    'email'           => 'antonio.garcia@university.edu',
                    'dept_code'       => 'BSBA',
                    'faculty_code'    => 'FAC-BSBA-003',
                    'biometric_id'    => 'BIO009',
                    'first_name'      => 'Antonio',
                    'middle_name'     => 'B.',
                    'last_name'       => 'Garcia',
                    'phone'           => '09191234009',
                    'employment_type' => 'part_time',
                    'date_hired'      => '2021-08-01',
                ],
                // ── BSED ──────────────────────────────────────────────────────
                [
                    'email'           => 'marisol.bautista@university.edu',
                    'dept_code'       => 'BSED',
                    'faculty_code'    => 'FAC-BSED-001',
                    'biometric_id'    => 'BIO010',
                    'first_name'      => 'Marisol',
                    'middle_name'     => 'N.',
                    'last_name'       => 'Bautista',
                    'phone'           => '09201234010',
                    'employment_type' => 'regular',
                    'date_hired'      => '2015-06-01',
                ],
                [
                    'email'           => 'ricardo.fernandez@university.edu',
                    'dept_code'       => 'BSED',
                    'faculty_code'    => 'FAC-BSED-002',
                    'biometric_id'    => 'BIO011',
                    'first_name'      => 'Ricardo',
                    'middle_name'     => 'V.',
                    'last_name'       => 'Fernandez',
                    'phone'           => '09201234011',
                    'employment_type' => 'regular',
                    'date_hired'      => '2018-08-01',
                ],
                [
                    'email'           => 'lourdes.aquino@university.edu',
                    'dept_code'       => 'BSED',
                    'faculty_code'    => 'FAC-BSED-003',
                    'biometric_id'    => 'BIO012',
                    'first_name'      => 'Lourdes',
                    'middle_name'     => 'T.',
                    'last_name'       => 'Aquino',
                    'phone'           => '09201234012',
                    'employment_type' => 'part_time',
                    'date_hired'      => '2023-01-05',
                ],
                // ── BSCE ──────────────────────────────────────────────────────
                [
                    'email'           => 'eduardo.navarro@university.edu',
                    'dept_code'       => 'BSCE',
                    'faculty_code'    => 'FAC-BSCE-001',
                    'biometric_id'    => 'BIO013',
                    'first_name'      => 'Eduardo',
                    'middle_name'     => 'S.',
                    'last_name'       => 'Navarro',
                    'phone'           => '09211234013',
                    'employment_type' => 'regular',
                    'date_hired'      => '2014-06-01',
                ],
                [
                    'email'           => 'cristina.morales@university.edu',
                    'dept_code'       => 'BSCE',
                    'faculty_code'    => 'FAC-BSCE-002',
                    'biometric_id'    => 'BIO014',
                    'first_name'      => 'Cristina',
                    'middle_name'     => 'F.',
                    'last_name'       => 'Morales',
                    'phone'           => '09211234014',
                    'employment_type' => 'regular',
                    'date_hired'      => '2017-08-01',
                ],
                [
                    'email'           => 'fernando.castro@university.edu',
                    'dept_code'       => 'BSCE',
                    'faculty_code'    => 'FAC-BSCE-003',
                    'biometric_id'    => 'BIO015',
                    'first_name'      => 'Fernando',
                    'middle_name'     => 'J.',
                    'last_name'       => 'Castro',
                    'phone'           => '09211234015',
                    'employment_type' => 'part_time',
                    'date_hired'      => '2022-06-01',
                ],
            ];

            foreach ($faculty as $data) {
                Faculty::firstOrCreate(
                    ['faculty_code' => $data['faculty_code']],
                    [
                        'user_id'         => $userIds[$data['email']],
                        'department_id'   => $deptIds[$data['dept_code']],
                        'faculty_code'    => $data['faculty_code'],
                        'biometric_id'    => $data['biometric_id'],
                        'first_name'      => $data['first_name'],
                        'middle_name'     => $data['middle_name'],
                        'last_name'       => $data['last_name'],
                        'phone'           => $data['phone'],
                        'employment_type' => $data['employment_type'],
                        'date_hired'      => $data['date_hired'],
                        'is_active'       => true,
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
