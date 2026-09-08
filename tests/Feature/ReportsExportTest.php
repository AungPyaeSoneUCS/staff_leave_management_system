<?php

use App\Models\Department;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\LeaveTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        DepartmentSeeder::class,
        LeaveTypeSeeder::class,
    ]);

    $this->admin = User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@example.edu',
        'role' => 'admin',
        'is_active' => true,
    ]);
});

it('exports a leave summary report as a downloadable pdf', function () {
    $staff = User::factory()->create([
        'name' => 'Jane Doe',
        'role' => 'staff',
        'staff_id' => 'ST001',
        'department_id' => Department::where('code', 'IT')->first()->id,
        'is_active' => true,
    ]);

    $leaveType = LeaveType::where('code', 'ANNUAL')->first();

    LeaveRequest::create([
        'user_id' => $staff->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->subDays(2),
        'total_days' => 4,
        'reason' => 'Vacation',
        'status' => 'approved',
        'reviewed_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.reports.export'), ['type' => 'leave_summary']);

    $response->assertOk();
    expect(str_starts_with($response->content(), '%PDF'))->toBeTrue();
    $response->assertHeader('Content-Type', 'application/pdf');
});

it('exports a staff leave balance report as a downloadable pdf', function () {
    $staff = User::factory()->create([
        'name' => 'John Doe',
        'role' => 'staff',
        'staff_id' => 'ST002',
        'department_id' => Department::where('code', 'HR')->first()->id,
        'is_active' => true,
    ]);

    $leaveType = LeaveType::where('code', 'MEDICAL')->first();

    LeaveBalance::create([
        'user_id' => $staff->id,
        'leave_type_id' => $leaveType->id,
        'year' => now()->year,
        'allocated_days' => 15,
        'used_days' => 5,
        'remaining_days' => 10,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.reports.export'), [
            'type' => 'balance',
            'year' => now()->year,
            'leave_type_id' => $leaveType->id,
        ]);

    $response->assertOk();
    expect(str_starts_with($response->content(), '%PDF'))->toBeTrue();
});

it('validates the report type and rejects unknown types', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.reports.export'), ['type' => 'unknown'])
        ->assertSessionHasErrors(['type']);
});

it('displays the reports page with populated filters', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.reports.leave-summary'));

    $response->assertOk();
    $response->assertViewHasAll(['departments', 'leaveTypes', 'currentYear']);
    $response->assertSeeText('Leave Summary Report', false);
});

it('requires authentication to export pdf reports', function () {
    $this->post(route('admin.reports.export'), ['type' => 'leave_summary'])
        ->assertRedirect(route('login'));
});

it('renders used and remaining values on the balance pdf chart', function () {
    $row = [
        'staff_name' => 'John Doe',
        'staff_id' => 'ST002',
        'department' => 'HR',
        'leave_type' => 'Medical',
        'allocated_days' => 15,
        'used_days' => 5,
        'remaining_days' => 10,
        'is_not_limited' => false,
    ];

    $html = view('admin.reports.pdf', [
        'type' => 'balance',
        'title' => 'Balance Report',
        'filterSummary' => [],
        'data' => [$row],
        'chart' => [
            'labels' => ['John Doe'],
            'used' => ['John Doe' => 5.0],
            'remaining' => ['John Doe' => 10.0],
            'colors' => ['#ef4444', '#22c55e'],
        ],
    ])->render();

    expect($html)
        ->toContain('font-weight="bold" font-family="notosansmyanmar">5</text>')
        ->toContain('font-weight="bold" font-family="notosansmyanmar">10</text>')
        ->not->toContain('font-weight="bold" font-family="notosansmyanmar">0</text>');
});

it('returns balance report data as json', function () {
    $staff = User::factory()->create([
        'name' => 'John Doe',
        'role' => 'staff',
        'staff_id' => 'ST002',
        'department_id' => Department::where('code', 'HR')->first()->id,
        'is_active' => true,
    ]);

    $leaveType = LeaveType::where('code', 'MEDICAL')->first();

    LeaveBalance::create([
        'user_id' => $staff->id,
        'leave_type_id' => $leaveType->id,
        'year' => now()->year,
        'allocated_days' => 15,
        'used_days' => 5,
        'remaining_days' => 10,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.balance-data', [
            'year' => now()->year,
            'leave_type_id' => $leaveType->id,
        ]));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'staff_name' => 'John Doe',
        'leave_type' => $leaveType->name,
        'allocated_days' => '15',
        'used_days' => '5',
        'remaining_days' => '10',
    ]);
});

it('returns leave summary report data as json', function () {
    $staff = User::factory()->create([
        'name' => 'Jane Doe',
        'role' => 'staff',
        'staff_id' => 'ST001',
        'department_id' => Department::where('code', 'IT')->first()->id,
        'is_active' => true,
    ]);

    $leaveType = LeaveType::where('code', 'ANNUAL')->first();
    $leaveStart = now()->subDays(5)->startOfDay();
    $leaveEnd = now()->subDays(2)->startOfDay();

    LeaveRequest::create([
        'user_id' => $staff->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => $leaveStart,
        'end_date' => $leaveEnd,
        'total_days' => 4,
        'reason' => 'Vacation',
        'status' => 'approved',
        'reviewed_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.leave-summary-data', [
            'department_id' => $staff->department_id,
            'start_date' => $leaveStart->toDateString(),
            'end_date' => $leaveEnd->toDateString(),
        ]));

    $response->assertOk();
    $response->assertJsonCount(1, 'table');
    $response->assertJsonFragment([
        'staff_name' => 'Jane Doe',
        'leave_type' => $leaveType->name,
        'total_days' => '4',
    ]);
});
