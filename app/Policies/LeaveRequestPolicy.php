<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'department_head', 'staff']);
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $leaveRequest->user->department_id === $user->department_id;
        }

        return $user->id === $leaveRequest->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isDepartmentHead();
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $leaveRequest->user->department_id === $user->department_id
                && $leaveRequest->isPending();
        }

        return $user->id === $leaveRequest->user_id && $leaveRequest->isPending();
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->id === $leaveRequest->user_id && $leaveRequest->isPending();
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        if (! $leaveRequest->isPending()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return $leaveRequest->current_approval_level === 3;
        }

        if ($user->isAdmin()) {
            return $leaveRequest->current_approval_level === 2
                && $user->isAssignedToRoutineDuty();
        }

        if ($user->isDepartmentHead()) {
            return $leaveRequest->user->department_id === $user->department_id
                && $leaveRequest->user->role === 'staff'
                && ! $leaveRequest->user->require_admin_approval
                && $leaveRequest->current_approval_level === 1;
        }

        return false;
    }
}
