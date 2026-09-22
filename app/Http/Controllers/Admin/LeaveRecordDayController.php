<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Services\LeaveWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRecordDayController extends Controller
{
    public function __construct(
        private LeaveBalanceService $balanceService,
        private LeaveWorkflowService $workflowService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_half_day' => ['nullable', 'boolean'],
        ]);

        $data = $this->normalizeDates($validated['start_date'], $validated['end_date'] ?? null, ! empty($validated['is_half_day']));

        if ($data === null) {
            return back()->with('error', __('flash.leave_record_day_invalid'));
        }

        $user = User::findOrFail($validated['user_id']);
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        $request = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $data['start'],
            'end_date' => $data['end'],
            'total_days' => $data['total'],
            'reason' => __('flash.leave_record_day_reason'),
            'status' => 'approved',
            'current_approval_level' => 2,
            'is_half_day' => $data['half'],
        ]);

        $this->balanceService->updateUsedDays($user, $leaveType, (float) $data['total']);

        return back()->with('success', __('flash.leave_record_day_added'));
    }

    public function update(Request $httpRequest, LeaveRequest $leaveRequest)
    {
        $validated = $httpRequest->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_half_day' => ['nullable', 'boolean'],
        ]);

        $data = $this->normalizeDates($validated['start_date'], $validated['end_date'] ?? null, ! empty($validated['is_half_day']));

        if ($data === null) {
            return back()->with('error', __('flash.leave_record_day_invalid'));
        }

        $oldTotal = (float) $leaveRequest->total_days;

        $leaveRequest->update([
            'start_date' => $data['start'],
            'end_date' => $data['end'],
            'total_days' => $data['total'],
            'is_half_day' => $data['half'],
        ]);

        $this->balanceService->updateUsedDays($leaveRequest->user, $leaveRequest->leaveType, (float) $data['total'] - $oldTotal);

        return back()->with('success', __('flash.leave_record_day_updated'));
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $this->balanceService->updateUsedDays($leaveRequest->user, $leaveRequest->leaveType, -(float) $leaveRequest->total_days);
        $leaveRequest->delete();

        return back()->with('success', __('flash.leave_record_day_deleted'));
    }

    /**
     * @return array{start: string, end: string, total: float, half: bool}|null
     */
    private function normalizeDates(string $startDate, ?string $endDate, bool $halfDay): ?array
    {
        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = $halfDay || $endDate === null || $endDate === ''
            ? $start
            : Carbon::parse($endDate)->format('Y-m-d');

        if (Carbon::parse($end)->lt(Carbon::parse($start))) {
            return null;
        }

        $total = $this->workflowService->calculateTotalDays($start, $end, $halfDay);

        if ($total <= 0) {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
            'total' => (float) $total,
            'half' => $halfDay,
        ];
    }
}
