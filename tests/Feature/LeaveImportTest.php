<?php

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

it('renders the leave import page for an admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.leave-import.index'))
        ->assertOk()
        ->assertSee('Import Leave Data');
});

it('downloads the leave import template', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.leave-import.template'));

    $response->assertOk();
    $this->assertStringContainsString('leave-history-import-template.csv', $response->headers->get('content-disposition'));
});

it('previews and imports leave history from a csv', function () {
    $leaveType = LeaveType::where('code', 'ANNUAL')->first();

    $staff = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.edu',
        'role' => 'staff',
        'staff_id' => 'ST001',
        'is_active' => true,
    ]);

    $cover = User::factory()->create([
        'name' => 'Bob Cover',
        'email' => 'bob@example.edu',
        'role' => 'staff',
        'staff_id' => 'ST002',
        'is_active' => true,
    ]);

    $csv = "staff_id,leave_type,start_date,end_date,total_days,is_half_day,status,duty_exchange\n"
        . "ST001,ANNUAL,2026-01-05,2026-01-07,,no,approved,\n"
        . "ST001,ANNUAL,2026-02-05,2026-02-05,0.5,yes,approved,ST002\n";

    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('leaves.csv', $csv);
    $file->mime = 'text/csv';

    $preview = $this->actingAs($this->admin)
        ->post(route('admin.leave-import.preview'), ['import_file' => $file]);

    $preview->assertSessionHas('leave_import_preview_data');

    $previewData = session('leave_import_preview_data');
    expect(count($previewData))->toBe(2);
    expect((float) $previewData[1]['total_days'])->toBe(0.5);
    expect($previewData[1]['duty_exchange_user_id'])->toBe($cover->id);
    expect($previewData[1]['duty_exchange_name'])->toBe($cover->name);
    expect($previewData[1]['duty_exchange_staff_id'])->toBe($cover->staff_id);

    $this->actingAs($this->admin)
        ->post(route('admin.leave-import.process'), [
            'rows' => ['0', '1'],
            'actions' => ['0' => 'import', '1' => 'import'],
        ])
        ->assertRedirect(route('admin.leave-import.index'))
        ->assertSessionHas('success');

    expect(LeaveRequest::where('user_id', $staff->id)->where('leave_type_id', $leaveType->id)->count())->toBe(2);
    expect(LeaveRequest::where('user_id', $staff->id)->where('duty_exchange_user_id', $cover->id)->exists())->toBeTrue();

    $balance = LeaveBalance::where('user_id', $staff->id)->where('leave_type_id', $leaveType->id)->first();
    expect((float) $balance->used_days)->toBe(3.5);
});

it('parses excel serial-number dates and leave type names in the preview', function () {
    $staff = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.edu',
        'role' => 'staff',
        'staff_id' => 'ST001',
        'is_active' => true,
    ]);

    // 2026-01-05 => 46027, 2026-01-07 => 46029 (Excel serial date cells)
    $csv = "staff_id,leave_type,start_date,end_date,total_days,is_half_day,status,duty_exchange\n"
        . "ST001,Annual Leave,46027,46029,,no,approved,\n";

    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('leaves.csv', $csv);
    $file->mime = 'text/csv';

    $preview = $this->actingAs($this->admin)
        ->post(route('admin.leave-import.preview'), ['import_file' => $file]);

    $preview->assertSessionHas('leave_import_preview_data');

    $previewData = session('leave_import_preview_data');
    expect(count($previewData))->toBe(1);
    expect($previewData[0]['start_date'])->toBe('2026-01-05');
    expect($previewData[0]['end_date'])->toBe('2026-01-07');
});

it('returns a helpful error when no rows are valid', function () {
    // staff ST999 does not exist
    $csv = "staff_id,leave_type,start_date,end_date,total_days,is_half_day,status,duty_exchange\n"
        . "ST999,ANNUAL,2026-01-05,2026-01-07,,no,approved,\n";

    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('leaves.csv', $csv);
    $file->mime = 'text/csv';

    $this->actingAs($this->admin)
        ->post(route('admin.leave-import.preview'), ['import_file' => $file])
        ->assertRedirect()
        ->assertSessionHas('error');
});
