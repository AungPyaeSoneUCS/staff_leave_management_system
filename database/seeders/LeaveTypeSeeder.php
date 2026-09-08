<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'ANNUAL',
                'description' => 'Annual vacation leave',
                'annual_allocation' => 30,
                'requires_attachment' => false,
                'is_active' => true,
            ],
[
                'name' => 'Maternity Leave',
                'code' => 'MATERNITY',
                'description' => 'Leave for maternity reasons',
                'annual_allocation' => 180,
                'requires_attachment' => true,
                'is_not_limited' => false,
                'track_on_use' => true,
                'is_active' => true,
            ],
[
                'name' => 'Study Leave',
                'code' => 'STUDY',
                'description' => 'Leave for academic pursuits',
                'annual_allocation' => 20,
                'requires_attachment' => true,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::create($type);
        }
    }
}
