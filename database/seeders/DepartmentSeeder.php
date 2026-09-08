<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Information Technology', 'code' => 'IT', 'description' => 'IT Department'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Human Resources Department'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Finance Department'],
            ['name' => 'Computer Science', 'code' => 'CS', 'description' => 'Computer Science Faculty'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
