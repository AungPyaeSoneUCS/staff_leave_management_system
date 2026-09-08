<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();

        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'staff_id' => 'ADM001',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Super System Administrator',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'staff_id' => 'ADM000',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Dr. Sarah Johnson',
            'email' => 'sarah.johnson@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'department_head',
            'department_id' => $itDepartment->id,
            'staff_id' => 'DH001',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Prof. Michael Brown',
            'email' => 'michael.brown@gmail',
            'password' => Hash::make('password'),
            'role' => 'department_head',
            'department_id' => $hrDepartment->id,
            'staff_id' => 'DH002',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'John Smith',
            'email' => 'john.smith@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'department_id' => $itDepartment->id,
            'staff_id' => 'ST001',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'John Smith1',
            'email' => 'john.smith1@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'department_id' => $itDepartment->id,
            'staff_id' => 'ST002',
            'is_active' => true,
        ]);
    }
}
