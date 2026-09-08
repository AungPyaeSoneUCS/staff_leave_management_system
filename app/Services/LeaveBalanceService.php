<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;

class LeaveBalanceService
{
    public function calculateAnnualLeaveBalance(User $user, int $year): array
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $balances = [];

        foreach ($leaveTypes as $leaveType) {
            $balance = $this->getOrCreateBalance($user, $leaveType, $year);

            if ($leaveType->track_on_use && $balance->used_days == 0) {
                continue;
            }

            $balances[] = [
                'leave_type' => $leaveType->name,
                'leave_type_mm' => $leaveType->name_mm,
                'allocated_days' => $balance->allocated_days,
                'used_days' => $balance->used_days,
                'remaining_days' => $balance->remaining_days,
                'is_not_limited' => $leaveType->is_not_limited,
            ];
        }

        return $balances;
    }

    public function getRemainingDays(User $user, LeaveType $leaveType, int $year): float
    {
        $balance = $this->getOrCreateBalance($user, $leaveType, $year);

        return (float) $balance->remaining_days;
    }

    public function updateUsedDays(User $user, LeaveType $leaveType, int|float $days): void
    {
        $year = now()->year;
        $balance = $this->getOrCreateBalance($user, $leaveType, $year);

        $balance->used_days = round((float) $balance->used_days + $days, 2);
        $balance->remaining_days = round(max(0, (float) $balance->allocated_days - (float) $balance->used_days), 2);
        $balance->save();
    }

    public function reallocateForLeaveType(LeaveType $leaveType, int $newAllocation): int
    {
        $updated = LeaveBalance::where('leave_type_id', $leaveType->id)
            ->whereYear('created_at', now()->year)
            ->get()
            ->each(function ($balance) use ($newAllocation) {
                $balance->allocated_days = $newAllocation;
                $balance->remaining_days = max(0, $newAllocation - $balance->used_days);
                $balance->save();
            });

        return $updated->count();
    }

    private function getOrCreateBalance(User $user, LeaveType $leaveType, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            [
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ],
            [
                'allocated_days' => $leaveType->annual_allocation,
                'used_days' => 0,
                'remaining_days' => $leaveType->annual_allocation,
            ]
        );
    }
}
