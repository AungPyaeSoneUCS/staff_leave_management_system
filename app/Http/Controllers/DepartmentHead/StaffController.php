<?php

namespace App\Http\Controllers\DepartmentHead;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $sortable = ['name', 'staff_id', 'position', 'role', 'is_active'];
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');

        if (!in_array($sort, $sortable, true)) {
            $sort = 'name';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = User::where('department_id', $departmentId)
            ->with('department');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('name_mm', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($position = $request->query('position')) {
            $query->where(function ($q) use ($position) {
                $q->where('position', $position)
                    ->orWhere('position_mm', $position);
            });
        }

        if ($sort === 'position') {
            $query->orderByRaw($direction === 'asc' ? 'position IS NOT NULL DESC' : 'position IS NOT NULL ASC')
                ->orderBy('position', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $query->orderBy('users.name');

        $staff = $query->paginate(15)->withQueryString();

        $positions = Config::get('positions', []);

        $nameSuggestions = User::where('department_id', $departmentId)
            ->get(['name', 'name_mm'])
            ->flatMap(fn ($user) => array_values(array_filter([$user->name, $user->name_mm])))
            ->unique()
            ->values();

        return view('department-head.staff.index', compact('staff', 'sort', 'direction', 'positions', 'nameSuggestions'));
    }

    public function show(User $user)
    {
        $departmentHead = auth()->user();

        if ($user->department_id !== $departmentHead->department_id) {
            abort(403);
        }

        $user->load('department', 'leaveBalances.leaveType', 'leaveRequests.leaveType');

        $balances = $user->leaveBalances
            ->filter(fn ($balance) => ! $balance->leaveType->track_on_use || (float) $balance->used_days > 0)
            ->values();

        return view('department-head.staff.show', compact('user', 'balances'));
    }
}
