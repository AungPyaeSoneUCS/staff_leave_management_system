<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::with('department')
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', compact('staff'));
    }

    public function show(User $user)
    {
        $user->load('department', 'leaveBalances.leaveType', 'leaveRequests.leaveType');

        $balances = $user->leaveBalances
            ->filter(fn ($balance) => ! $balance->leaveType->track_on_use || (float) $balance->used_days > 0)
            ->values();

        return view('admin.staff.show', compact('user', 'balances'));
    }
}
