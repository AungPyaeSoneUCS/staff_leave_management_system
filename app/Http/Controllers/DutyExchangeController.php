<?php

namespace App\Http\Controllers;

use App\Models\AdminAssignment;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\DutyExchangeRequestNotification;
use App\Notifications\LeaveRequestStatusUpdatedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use Illuminate\Http\Request;

class DutyExchangeController extends Controller
{
    public function index()
    {
        $pendingRequests = LeaveRequest::where('duty_exchange_user_id', auth()->id())
            ->where('status', 'pending')
            ->where('duty_exchange_status', 'pending')
            ->with('user', 'leaveType', 'user.department')
            ->latest()
            ->paginate(10);

        return view('duty-exchange.index', compact('pendingRequests'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        if (! $this->canReview($leaveRequest)) {
            abort(403);
        }

        $leaveRequest->load('user', 'leaveType', 'reviewer', 'hr', 'super_admin', 'user.department', 'dutyExchangeUser');

        return view('duty-exchange.show', compact('leaveRequest'));
    }

    public function accept(LeaveRequest $leaveRequest, Request $request)
    {
        if (! $this->canReview($leaveRequest) || ! $leaveRequest->isAwaitingDutyExchange()) {
            abort(403);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $leaveRequest->update([
            'duty_exchange_status' => 'accepted',
            'duty_exchange_confirmed_at' => now(),
            'duty_exchange_remarks' => $validated['remarks'] ?? null,
        ]);

        $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'duty_exchange_accepted'));

        $this->forwardToNextApprover($leaveRequest);

        return redirect()->route('duty-exchange.index')
            ->with('success', __('duty_exchange.accept_success'));
    }

    public function reject(LeaveRequest $leaveRequest, Request $request)
    {
        if (! $this->canReview($leaveRequest) || ! $leaveRequest->isAwaitingDutyExchange()) {
            abort(403);
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'duty_exchange_status' => 'rejected',
            'duty_exchange_confirmed_at' => now(),
            'duty_exchange_remarks' => $validated['remarks'],
        ]);

        $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'rejected'));

        return redirect()->route('duty-exchange.index')
            ->with('success', __('duty_exchange.reject_success'));
    }

    private function canReview(LeaveRequest $leaveRequest): bool
    {
        return auth()->id() === $leaveRequest->duty_exchange_user_id;
    }

    private function forwardToNextApprover(LeaveRequest $leaveRequest): void
    {
        $requester = $leaveRequest->user;

        if ($requester->isDepartmentHead() || $requester->require_admin_approval) {
            User::where('role', 'admin')
                ->whereIn('id', AdminAssignment::select('admin_id'))
                ->get()
                ->each(function ($admin) use ($leaveRequest) {
                    $admin->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'admin'));
                });

            return;
        }

        $departmentHeadId = $requester->department?->head_id;
        $departmentHead = $departmentHeadId ? User::find($departmentHeadId) : null;

        if ($departmentHead && $departmentHead->id !== $requester->id) {
            $departmentHead->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'department_head'));
        }

        if ($departmentHead && $departmentHead->id === $requester->id) {
            User::where('role', 'admin')
                ->whereIn('id', AdminAssignment::select('admin_id'))
                ->get()
                ->each(function ($admin) use ($leaveRequest) {
                    $admin->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'admin'));
                });
        }
    }
}