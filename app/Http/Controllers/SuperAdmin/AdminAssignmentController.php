<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminAssignment;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\AdminAssignedNotification;
use Illuminate\Http\Request;

class AdminAssignmentController extends Controller
{
    public function index()
    {
        $assignments = AdminAssignment::with('admin')
            ->latest()
            ->paginate(20);

        $admins = User::where('role', 'admin')
            ->where('is_active', true)
            ->get();

        return view('super-admin.assignments.index', compact('assignments', 'admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $admin = User::where('role', 'admin')->findOrFail($validated['admin_id']);

        if (! $admin->is_active) {
            return back()->with('error', __('flash.user_deactivated'));
        }

        AdminAssignment::updateOrCreate(
            ['admin_id' => $admin->id],
            ['reason' => $validated['reason']]
        );

        $admin->notify(new AdminAssignedNotification($admin, $validated['reason']));

        return back()->with('success', __('flash.assignment_created', ['count' => 1]));
    }

    public function destroy(AdminAssignment $adminAssignment)
    {
        $adminAssignment->delete();

        return back()->with('success', __('flash.assignment_removed'));
    }

    public function pending()
    {
        $pendingLevel2 = LeaveRequest::where('status', 'pending')
            ->where('current_approval_level', 2)
            ->with('user', 'leaveType', 'user.department')
            ->latest()
            ->paginate(20);

        return view('super-admin.assignments.pending', compact('pendingLevel2'));
    }
}
