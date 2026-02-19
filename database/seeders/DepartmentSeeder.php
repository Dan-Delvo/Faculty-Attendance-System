<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $departments = [
                [
                    'code'        => 'BSCS',
                    'name'        => 'Bachelor of Science in Computer Science',
                    'description' => 'Department of Computer Science offering foundational and advanced CS courses.',
                    'is_active'   => true,
                ],
                [
                    'code'        => 'BSIT',
                    'name'        => 'Bachelor of Science in Information Technology',
                    'description' => 'Department of Information Technology focusing on practical IT applications.',
                    'is_active'   => true,
                ],
                [
                    'code'        => 'BSBA',
                    'name'        => 'Bachelor of Science in Business Administration',
                    'description' => 'Department of Business Administration covering management and entrepreneurship.',
                    'is_active'   => true,
                ],
                [
                    'code'        => 'BSED',
                    'name'        => 'Bachelor of Secondary Education',
                    'description' => 'Department of Education focused on secondary-level teacher training.',
                    'is_active'   => true,
                ],
                [
                    'code'        => 'BSCE',
                    'name'        => 'Bachelor of Science in Civil Engineering',
                    'description' => 'Department of Civil Engineering covering structural and environmental engineering.',
                    'is_active'   => true,
                ],
            ];

            foreach ($departments as $department) {
                Department::firstOrCreate(['code' => $department['code']], $department);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
