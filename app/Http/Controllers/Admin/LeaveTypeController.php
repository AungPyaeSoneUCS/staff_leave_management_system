<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct(
        private LeaveBalanceService $leaveBalanceService
    ) {}

    public function index()
    {
        $leaveTypes = LeaveType::paginate(10);

        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('admin.leave-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'code' => 'required|string|unique:leave_types,code',
            'description' => 'nullable|string',
            'description_mm' => 'nullable|string',
            'annual_allocation' => 'nullable|integer|min:1',
            'per_leave_days' => 'nullable|integer|min:1',
            'requires_attachment' => 'boolean',
            'is_not_limited' => 'boolean',
            'track_on_use' => 'boolean',
        ]);

        $isNotLimited = ! empty($validated['is_not_limited']);

        if ($isNotLimited) {
            $validated['annual_allocation'] = 0;
        } elseif (empty($validated['annual_allocation'])) {
            return back()->with('error', __('flash.annual_allocation_required'))->withInput();
        }

        $validated['is_not_limited'] = $isNotLimited;
        $validated['track_on_use'] = ! empty($validated['track_on_use']);

        LeaveType::create($validated);

        return redirect()->route('admin.leave-types.index')
            ->with('success', __('flash.leave_type_created'));
    }

    public function edit(LeaveType $leaveType)
    {
        return view('admin.leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'code' => 'required|string|unique:leave_types,code,'.$leaveType->id,
            'description' => 'nullable|string',
            'description_mm' => 'nullable|string',
            'annual_allocation' => 'nullable|integer|min:1',
            'per_leave_days' => 'nullable|integer|min:1',
            'requires_attachment' => 'boolean',
            'is_not_limited' => 'boolean',
            'track_on_use' => 'boolean',
        ]);

        $isNotLimited = ! empty($validated['is_not_limited']);

        if ($isNotLimited) {
            $validated['annual_allocation'] = 0;
        } elseif (empty($validated['annual_allocation'])) {
            return back()->with('error', __('flash.annual_allocation_required'))->withInput();
        }

        $validated['is_not_limited'] = $isNotLimited;
        $validated['track_on_use'] = ! empty($validated['track_on_use']);

        $leaveType->update($validated);

        return redirect()->route('admin.leave-types.index')
            ->with('success', __('flash.leave_type_updated'));
    }

    public function reallocateBalances(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'new_allocation' => 'required|integer|min:1',
        ]);

        $count = $this->leaveBalanceService->reallocateForLeaveType($leaveType, (int) $request->input('new_allocation'));

        return back()->with('success', __('flash.leave_type_reallocated', ['count' => $count]));
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();

        return redirect()->route('admin.leave-types.index')
            ->with('success', __('flash.leave_type_deleted'));
    }
}
