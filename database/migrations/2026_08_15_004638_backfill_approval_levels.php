<?php

use App\Models\LeaveRequest;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        LeaveRequest::where('status', 'pending')
            ->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('role', 'department_head');
                })
                    ->orWhereHas('user', function ($q) {
                        $q->where('require_admin_approval', true);
                    });
            })
            ->update(['current_approval_level' => 2]);
    }

    public function down(): void
    {
        LeaveRequest::where('status', 'pending')
            ->whereHas('user', function ($q) {
                $q->where('role', 'department_head');
            })
            ->update(['current_approval_level' => 1]);
    }
};
