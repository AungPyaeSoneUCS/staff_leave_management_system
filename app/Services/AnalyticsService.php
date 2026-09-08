<?php

namespace App\Services;

use App\Models\Department;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    private function localizedName(string $name, ?string $nameMm): string
    {
        return app()->getLocale() === 'my' && ! empty($nameMm) ? $nameMm : $name;
    }

    public function getDashboardStatistics(?User $user = null): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $pendingLevel = $user?->isSuperAdmin() ? 3 : 2;

        return [
            'total_staff' => User::where('role', 'staff')->count(),
            'total_departments' => Department::count(),
            'pending_requests' => LeaveRequest::where('status', 'pending')
                ->where('current_approval_level', $pendingLevel)
                ->count(),
            'approved_today' => LeaveRequest::where('status', 'approved')
                ->whereDate('reviewed_at', $today)
                ->count(),
            'rejected_today' => LeaveRequest::where('status', 'rejected')
                ->whereDate('reviewed_at', $today)
                ->count(),
            'approved_this_month' => LeaveRequest::where('status', 'approved')
                ->whereDate('reviewed_at', '>=', $monthStart)
                ->count(),
            'rejected_this_month' => LeaveRequest::where('status', 'rejected')
                ->whereDate('reviewed_at', '>=', $monthStart)
                ->count(),
        ];
    }

    public function getLeaveStatisticsByType(array $filters = []): array
    {
        $query = LeaveRequest::query()
            ->with(['leaveType'])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters) {
                $query->whereDate('start_date', '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters) {
                $query->whereDate('end_date', '<=', $filters['end_date']);
            })
            ->when(! empty($filters['year']), function ($query) use ($filters) {
                $query->whereYear('start_date', $filters['year']);
            })
            ->where('status', 'approved')
            ->groupBy('leave_type_id');

        return $query->select('leave_type_id', DB::raw('SUM(total_days) as total_days'))
            ->with('leaveType')
            ->get()
            ->map(function ($item) {
                return [
                    'leave_type' => $this->localizedName($item->leaveType->name, $item->leaveType->name_mm),
                    'total_days' => (float) $item->total_days,
                    'is_not_limited' => $item->leaveType->is_not_limited,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getDepartmentLeaveStatistics(array $filters = []): array
    {
        $query = LeaveRequest::query()
            ->with(['user.department', 'leaveType'])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters) {
                $query->whereDate('start_date', '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters) {
                $query->whereDate('end_date', '<=', $filters['end_date']);
            })
            ->when(! empty($filters['year']), function ($query) use ($filters) {
                $query->whereYear('start_date', $filters['year']);
            })
            ->where('status', 'approved');

        $grouped = $query->get()->groupBy('user.department.name')->map(function ($items) {
            $total = 0;
            foreach ($items as $item) {
                $total += $item->leaveType->is_not_limited ? 0 : $item->total_days;
            }

            return $total;
        });

        return $grouped->map(function ($total, $department) {
            $deptName = $department;
            if (app()->getLocale() === 'my') {
                $deptModel = Department::where('name', $department)->first();
                $deptName = $deptModel ? ($deptModel->name_mm ?? $deptModel->name) : $department;
            }

            return [
                'department' => $deptName,
                'total_days' => (float) $total,
                'is_not_limited' => false,
            ];
        })->values()->toArray();
    }

    public function getLeaveSummary(array $filters = []): array
    {
        $query = LeaveRequest::query()
            ->with(['user.department', 'leaveType', 'reviewer'])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters) {
                $query->whereDate('start_date', '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters) {
                $query->whereDate('end_date', '<=', $filters['end_date']);
            })
            ->when(! empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->orderByDesc('start_date');

        return $query->get()->map(function ($item) {
            return [
                'staff_name' => $this->localizedName($item->user->name, $item->user->name_mm),
                'staff_id' => $item->user->staff_id ?? '—',
                'department' => $item->user->department ? $this->localizedName($item->user->department->name, $item->user->department->name_mm) : '—',
                'leave_type' => $this->localizedName($item->leaveType->name, $item->leaveType->name_mm),
                'start_date' => $item->start_date->format('Y-m-d'),
                'end_date' => $item->end_date?->format('Y-m-d') ?? '—',
                'total_days' => $item->total_days,
                'is_not_limited' => $item->leaveType->is_not_limited,
                'status' => $item->status,
                'reviewer' => $item->reviewer ? $this->localizedName($item->reviewer->name, $item->reviewer->name_mm) : '—',
                'reviewed_at' => $item->reviewed_at ? $item->reviewed_at->format('Y-m-d') : '—',
                'profile_image' => $item->user->profile_image,
            ];
        })->values()->toArray();
    }

    public function getLeaveBalances(array $filters = []): array
    {
        $year = $filters['year'] ?? (int) now()->year;

        $staffQuery = User::where(function ($q) {
                $q->where('role', 'staff')->orWhere('role', 'department_head');
            })
            ->with('department');

        if (! empty($filters['department_id'])) {
            $staffQuery->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['staff_name'])) {
            $staffQuery->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['staff_name'].'%')
                    ->when(app()->getLocale() === 'my', function ($q2) use ($filters) {
                        $q2->orWhere('name_mm', 'like', '%'.$filters['staff_name'].'%');
                    });
            });
        }

        $staff = $staffQuery->orderBy('name')->get();

        $leaveTypeQuery = LeaveType::where('is_active', true);
        if (! empty($filters['leave_type_id'])) {
            $leaveTypeQuery->where('id', $filters['leave_type_id']);
        }
        $leaveTypes = $leaveTypeQuery->get();

        $existingBalances = LeaveBalance::where('year', $year)
            ->whereIn('user_id', $staff->pluck('id'))
            ->whereIn('leave_type_id', $leaveTypes->pluck('id'))
            ->get()
            ->keyBy(fn ($b) => $b->user_id.'_'.$b->leave_type_id);

        $data = [];
        foreach ($staff as $user) {
            foreach ($leaveTypes as $leaveType) {
                $balance = $existingBalances->get($user->id.'_'.$leaveType->id);

                if ($leaveType->track_on_use && (! $balance || $balance->used_days == 0)) {
                    continue;
                }

                $data[] = [
                    'staff_name' => $this->localizedName($user->name, $user->name_mm),
                    'staff_id' => $user->staff_id ?? '—',
                    'department' => $user->department ? $this->localizedName($user->department->name, $user->department->name_mm) : '—',
                    'leave_type' => $this->localizedName($leaveType->name, $leaveType->name_mm),
                    'allocated_days' => $balance ? (float) $balance->allocated_days : (float) $leaveType->annual_allocation,
                    'used_days' => $balance ? (float) $balance->used_days : 0,
                    'remaining_days' => $balance ? (float) $balance->remaining_days : (float) $leaveType->annual_allocation,
                    'is_not_limited' => $leaveType->is_not_limited,
                    'profile_image' => $user->profile_image,
                ];
            }
        }

        return $data;
    }

    public function getDepartmentAnalytics(array $filters = []): array
    {
        $year = $filters['year'] ?? now()->year;

        $query = Department::query()
            ->withCount(['users as staff_count' => function ($query) {
                $query->where('is_active', true);
            }])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->where('id', $filters['department_id']);
            })
            ->addSelect(['total_leave_days' => LeaveRequest::selectRaw('SUM(total_days)')
                ->join('users', 'leave_requests.user_id', '=', 'users.id')
                ->whereColumn('users.department_id', 'departments.id')
                ->where('leave_requests.status', 'approved')
                ->whereYear('leave_requests.start_date', $year)
                ->when(! empty($filters['month']), function ($q) use ($filters) {
                    $q->whereMonth('leave_requests.start_date', $filters['month']);
                }),
            ]);

        return $query->get()->map(function ($department) {
            $deptName = app()->getLocale() === 'my' ? ($department->name_mm ?? $department->name) : $department->name;

            return [
                'department' => $deptName,
                'staff_count' => $department->staff_count,
                'total_leave_days' => $department->total_leave_days ?? 0,
            ];
        })->values()->toArray();
    }
}
