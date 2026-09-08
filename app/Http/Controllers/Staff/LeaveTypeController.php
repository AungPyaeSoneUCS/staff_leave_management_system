<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('staff.leave-types.index', compact('leaveTypes'));
    }

    public function show(LeaveType $leaveType)
    {
        if (! $leaveType->is_active) {
            abort(404);
        }

        return view('staff.leave-types.show', compact('leaveType'));
    }
}
