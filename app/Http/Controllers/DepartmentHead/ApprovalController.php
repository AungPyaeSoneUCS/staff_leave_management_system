<?php

namespace App\Http\Controllers\DepartmentHead;

use App\Http\Controllers\Controller;
use App\Models\AdminAssignment;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveRequestStatusUpdatedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use App\Services\LeaveWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApprovalController extends Controller
{
    public function __construct(
        private LeaveWorkflowService $leaveWorkflowService,
    ) {}

    public function pendingRequests(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        $query = LeaveRequest::where('status', 'pending')
            ->where('current_approval_level', 1)
            ->whereHas('user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId)
                    ->where('require_admin_approval', false);
            })
            ->with('user', 'leaveType', 'dutyExchangeUser');

        if ($search = $request->query('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            });
        }

        if ($leaveTypeId = $request->query('leave_type_id')) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        $pendingRequests = $query->latest()->paginate(10);

        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('department-head.approvals.pending', compact('pendingRequests', 'leaveTypes'));
    }

    public function approve(LeaveRequest $leaveRequest, Request $request)
    {
        if (Gate::denies('approve', $leaveRequest)) {
            abort(403);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
            'signature' => 'required|string',
        ]);

        $this->leaveWorkflowService->processApproval(
            $leaveRequest,
            auth()->user(),
            'approved',
            $validated['remarks'] ?? null,
            null,
            $validated['signature']
        );

        $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'pending_hr'));

        User::where('role', 'admin')
            ->whereIn('id', AdminAssignment::select('admin_id'))
            ->get()
            ->each(function ($admin) use ($leaveRequest) {
                $admin->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'admin'));
            });

        return redirect()->route('department-head.approvals.pending')
            ->with('success', __('flash.agree_success'));
    }

    public function reject(LeaveRequest $leaveRequest, Request $request)
    {
        if (Gate::denies('approve', $leaveRequest)) {
            abort(403);
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:500',
            'signature' => 'required|string',
        ]);

        $this->leaveWorkflowService->processApproval(
            $leaveRequest,
            auth()->user(),
            'rejected',
            $validated['remarks'],
            null,
            $validated['signature']
        );

        $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'rejected'));

        return redirect()->route('department-head.approvals.pending')
            ->with('success', __('flash.reject_success'));
    }

    public function revoke(LeaveRequest $leaveRequest, Request $request)
    {
        if (Gate::denies('approve', $leaveRequest)) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->leaveWorkflowService->revokeApproval(
            $leaveRequest,
            auth()->user(),
            $validated['reason']
        );

        $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'revoked'));

        return redirect()->route('department-head.approvals.pending')
            ->with('success', __('flash.revoke_success'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        if (Gate::denies('view', $leaveRequest)) {
            abort(403);
        }

        $leaveRequest->load('user', 'leaveType', 'reviewer', 'hr', 'super_admin', 'user.department', 'dutyExchangeUser');

        return view('department-head.approvals.show', compact('leaveRequest'));
    }

    public function history(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        $query = LeaveRequest::where('reviewer_id', auth()->id())
            ->whereHas('user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->with('user', 'leaveType', 'user.department', 'dutyExchangeUser');

        if ($startDate = $request->query('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $processedRequests = $query->latest()->paginate(10);

        return view('department-head.approvals.history', compact('processedRequests'))->with('filters', $request->only(['start_date', 'end_date']));
    }
}
