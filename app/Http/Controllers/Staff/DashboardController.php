<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\LeaveBalanceService;

class DashboardController extends Controller
{
    public function __construct(
        private LeaveBalanceService $leaveBalanceService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $leaveBalances = $this->leaveBalanceService->calculateAnnualLeaveBalance($user, now()->year);
        $recentRequests = $user->leaveRequests()->with('leaveType')->latest()->take(5)->get();
        $pendingCount = $user->leaveRequests()->where('status', 'pending')->count();

        return view('staff.dashboard', compact('leaveBalances', 'recentRequests', 'pendingCount'));
    }
}
