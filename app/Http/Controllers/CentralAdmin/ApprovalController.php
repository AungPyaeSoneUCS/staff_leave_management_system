<?php

namespace App\Http\Controllers\CentralAdmin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestStatusUpdatedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use App\Services\LeaveWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
    public function __construct(
        private LeaveWorkflowService $leaveWorkflowService,
    ) {}

    public function pendingRequests(Request $request)
    {
        $query = LeaveRequest::where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('duty_exchange_user_id')
                    ->orWhere('duty_exchange_status', 'accepted');
            })
            ->with('user', 'leaveType', 'user.department', 'reviewer', 'dutyExchangeUser');

        if (auth()->user()->isSuperAdmin()) {
            $query->where('current_approval_level', 3);
        } else {
            $query->where('current_approval_level', 2);

            if (! auth()->user()->isAssignedToRoutineDuty()) {
                $query->where('id', 0);
            }
        }

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

        return view('central-admin.approvals.pending', compact('pendingRequests'));
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

        $result = $this->leaveWorkflowService->processCentralApproval(
            $leaveRequest,
            auth()->user(),
            'approved',
            $validated['remarks'] ?? null,
            $validated['signature']
        );

        if ($result === 'pending_super_admin') {
            $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'pending_super_admin'));

            $superAdmin = User::where('role', 'super_admin')->first();

            if ($superAdmin) {
                $superAdmin->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'super_admin'));
            }
        } else {
            $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, $result));
        }

        return redirect()->route('central-admin.approvals.pending')
            ->with('success', __('flash.approve_success'));
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

        $this->leaveWorkflowService->processCentralApproval(
            $leaveRequest,
            auth()->user(),
            'rejected',
            $validated['remarks'],
            $validated['signature']
        );

        $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'rejected'));

        return redirect()->route('central-admin.approvals.pending')
            ->with('success', __('flash.reject_success'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        if (Gate::denies('view', $leaveRequest)) {
            abort(403);
        }

        $leaveRequest->load('user', 'leaveType', 'reviewer', 'hr', 'super_admin', 'user.department');

        return view('central-admin.approvals.show', compact('leaveRequest'));
    }

    public function history(Request $request)
    {
        $processedRequests = LeaveRequest::where(function ($query) {
            if (auth()->user()->isSuperAdmin()) {
                $query->where('super_admin_id', auth()->id())
                    ->orWhere('cancelled_by_id', auth()->id());
            } else {
                $query->where('hr_id', auth()->id())
                    ->orWhere('cancelled_by_id', auth()->id());
            }
        })
            ->when($search = $request->query('search'), function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_mm', 'like', "%{$search}%")
                        ->orWhere('staff_id', 'like', "%{$search}%");
                });
            })
            ->when($leaveTypeId = $request->query('leave_type_id'), function ($query) use ($leaveTypeId) {
                $query->where('leave_type_id', $leaveTypeId);
            })
            ->when($startDate = $request->query('start_date'), function ($query) use ($startDate) {
                $query->whereDate('start_date', '>=', $startDate);
            })
            ->when($endDate = $request->query('end_date'), function ($query) use ($endDate) {
                $query->whereDate('end_date', '<=', $endDate);
            })
            ->with('user', 'leaveType', 'user.department', 'dutyExchangeUser')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('central-admin.approvals.history', compact('processedRequests'));
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'exists:leave_requests,id',
            'bulk_action' => 'required|in:approve,reject',
            'bulk_remarks' => 'nullable|string|max:500',
        ]);

        $status = $validated['bulk_action'] === 'approve' ? 'approved' : 'rejected';
        $processed = 0;

        foreach ($validated['selected'] as $requestId) {
            $leaveRequest = LeaveRequest::where('status', 'pending')
                ->where('current_approval_level', auth()->user()->isSuperAdmin() ? 3 : 2)
                ->findOrFail($requestId);

            if (Gate::denies('approve', $leaveRequest)) {
                continue;
            }

            $result = $this->leaveWorkflowService->processCentralApproval(
                $leaveRequest,
                auth()->user(),
                $status,
                $validated['bulk_remarks']
            );

            if ($result === 'pending_super_admin') {
                $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'pending_super_admin'));

                $superAdmin = User::where('role', 'super_admin')->first();

                if ($superAdmin) {
                    $superAdmin->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'super_admin'));
                }
            } else {
                $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, $result));
            }

            $processed++;
        }

        return redirect()->route('central-admin.approvals.pending')
            ->with('success', __('flash.bulk_processed', ['count' => $processed]));
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

        return redirect()->route('central-admin.approvals.pending')
            ->with('success', __('flash.revoke_success'));
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'exists:leave_requests,id',
        ]);

        $deleted = 0;

        foreach ($validated['selected'] as $requestId) {
            $leaveRequest = LeaveRequest::find($requestId);

            if (! $leaveRequest || Gate::denies('delete', $leaveRequest)) {
                continue;
            }

            if ($leaveRequest->attachment_path) {
                $paths = json_decode($leaveRequest->attachment_path, true) ?: [];
                foreach ($paths as $path) {
                    Storage::disk('public')->delete($path);
                }
            }

            $leaveRequest->delete();
            $deleted++;
        }

        return redirect()->route('central-admin.approvals.history')
            ->with('success', __('flash.bulk_deleted', ['count' => $deleted]));
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        if (Gate::denies('delete', $leaveRequest)) {
            abort(403);
        }

        if ($leaveRequest->attachment_path) {
            $paths = json_decode($leaveRequest->attachment_path, true) ?: [];
            foreach ($paths as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $leaveRequest->delete();

        return redirect()->route('central-admin.approvals.history')
            ->with('success', __('flash.request_deleted'));
    }
}
