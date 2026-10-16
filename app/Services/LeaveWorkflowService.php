<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;

class LeaveWorkflowService
{
    public function __construct(
        private LeaveBalanceService $leaveBalanceService,
    ) {}

    public function processApproval(LeaveRequest $leaveRequest, User $reviewer, string $status, ?string $remarks = null, ?int $nextApprovalLevel = null, ?string $signature = null): LeaveRequest
    {
        if ($status === 'rejected') {
            $leaveRequest->status = 'rejected';
            $leaveRequest->reviewer_id = $reviewer->id;
            $leaveRequest->reviewer_remarks = $remarks;
            $leaveRequest->reviewer_signature = $signature;
            $leaveRequest->reviewed_at = Carbon::now();

            $leaveRequest->save();

            return $leaveRequest;
        }

        $leaveRequest->reviewer_id = $reviewer->id;
        $leaveRequest->reviewer_remarks = $remarks;
        $leaveRequest->reviewer_signature = $signature;
        $leaveRequest->reviewed_at = Carbon::now();

        if ($nextApprovalLevel !== null) {
            $leaveRequest->current_approval_level = $nextApprovalLevel;
        } elseif ($leaveRequest->current_approval_level === 1 && $status === 'approved') {
            $leaveRequest->current_approval_level = 2;
        } elseif ($leaveRequest->current_approval_level === 2 && $status === 'approved') {
            $leaveRequest->status = 'approved';
            $this->leaveBalanceService->updateUsedDays(
                $leaveRequest->user,
                $leaveRequest->leaveType,
                $leaveRequest->total_days
            );
        }

        $leaveRequest->save();

        return $leaveRequest;
    }

    /**
     * Handles the central phase of the workflow.
     * - Admin (level 2) approval forwards the request to the super admin (level 3).
     * - Super admin (level 3) approval finalizes the request.
     * - Any reject terminates the request.
     *
     * Returns the notification key for the requester: 'approved', 'rejected' or 'pending_super_admin'.
     */
    public function processCentralApproval(LeaveRequest $leaveRequest, User $approver, string $status, ?string $remarks = null, ?string $signature = null): string
    {
        if ($status === 'rejected') {
            if ($approver->isSuperAdmin()) {
                $leaveRequest->super_admin_id = $approver->id;
                $leaveRequest->super_admin_signature = $signature;
                $leaveRequest->super_admin_remarks = $remarks;
            } else {
                $leaveRequest->hr_id = $approver->id;
                $leaveRequest->hr_signature = $signature;
                $leaveRequest->hr_remarks = $remarks;
            }

            $leaveRequest->status = 'rejected';
            $leaveRequest->reviewed_at = Carbon::now();
            $leaveRequest->save();

            return 'rejected';
        }

        if ($approver->isSuperAdmin()) {
            $leaveRequest->status = 'approved';
            $leaveRequest->super_admin_id = $approver->id;
            $leaveRequest->super_admin_signature = $signature;
            $leaveRequest->super_admin_remarks = $remarks;
            $leaveRequest->reviewed_at = Carbon::now();

            $this->leaveBalanceService->updateUsedDays(
                $leaveRequest->user,
                $leaveRequest->leaveType,
                $leaveRequest->total_days
            );

            $leaveRequest->save();

            return 'approved';
        }

        $leaveRequest->current_approval_level = 3;
        $leaveRequest->hr_id = $approver->id;
        $leaveRequest->hr_signature = $signature;
        $leaveRequest->hr_remarks = $remarks;
        $leaveRequest->reviewed_at = Carbon::now();
        $leaveRequest->save();

        return 'pending_super_admin';
    }

    public function approveDirectly(LeaveRequest $leaveRequest, User $approver, ?string $remarks = null): LeaveRequest
    {
        $leaveRequest->status = 'approved';
        $leaveRequest->reviewer_id = $approver->id;
        $leaveRequest->reviewer_remarks = $remarks;
        $leaveRequest->reviewed_at = Carbon::now();

        $this->leaveBalanceService->updateUsedDays(
            $leaveRequest->user,
            $leaveRequest->leaveType,
            $leaveRequest->total_days
        );

        $leaveRequest->save();

        return $leaveRequest;
    }

    public function revokeApproval(LeaveRequest $leaveRequest, User $cancelledBy, ?string $reason = null): LeaveRequest
    {
        $leaveRequest->status = 'revoked';
        $leaveRequest->cancelled_by_id = $cancelledBy->id;
        $leaveRequest->cancelled_at = Carbon::now();
        $leaveRequest->cancellation_reason = $reason;

        if ($leaveRequest->isApproved()) {
            $this->leaveBalanceService->updateUsedDays(
                $leaveRequest->user,
                $leaveRequest->leaveType,
                -$leaveRequest->total_days
            );
        }

        $leaveRequest->save();

        return $leaveRequest;
    }

    public function calculateTotalDays(string $startDate, string $endDate, bool $halfDay = false): int|float
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // All calendar days (including Saturdays and Sundays) count as leave
        // days. Only registered public holidays (with a concrete date) are
        // excluded from the total.
        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($holiday) => $holiday->date->toDateString());

        $totalDays = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($holidays->has($date->toDateString())) {
                continue;
            }

            $totalDays++;
        }

        if ($halfDay) {
            return $totalDays / 2;
        }

        return $totalDays;
    }

    public function validateLeaveBalance(User $user, int $leaveTypeId, int|float $requestedDays): bool
    {
        $leaveType = LeaveType::findOrFail($leaveTypeId);
        $remainingDays = $this->leaveBalanceService->getRemainingDays($user, $leaveType, now()->year);

        return $remainingDays >= $requestedDays;
    }

    public function validateNoOverlap(User $user, string $startDate, string $endDate, ?int $excludeId = null): bool
    {
        $query = $user->leaveRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->doesntExist();
    }
}
