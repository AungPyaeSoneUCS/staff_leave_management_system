<?php

namespace App\Http\Controllers\Staff;

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
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function __construct(
        private LeaveWorkflowService $leaveWorkflowService,
    ) {}

    private function viewPrefix(): string
    {
        return auth()->user()->isDepartmentHead() ? 'department-head.leave-requests' : 'staff.leave-requests';
    }

    private function routePrefix(): string
    {
        return auth()->user()->isDepartmentHead() ? 'department-head.leave-requests' : 'staff.leave-requests';
    }

    public function index(Request $request)
    {
        $query = auth()->user()->leaveRequests()
            ->with('leaveType', 'reviewer', 'user.department', 'dutyExchangeUser');

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->latest()->paginate(10)->appends($request->all());
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view($this->viewPrefix().'.index', compact('leaveRequests', 'leaveTypes'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $existingLeaves = auth()->user()->leaveRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->get(['start_date', 'end_date']);

        return view($this->viewPrefix().'.create', compact('leaveTypes', 'existingLeaves'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date',
            'reason' => 'required|string|max:500',
            'signature' => 'required|string',
            'attachments' => [
                'nullable',
                'array',
                'max:3',
                function ($attribute, $files, $fail) {
                    if (collect($files)->sum(fn ($file) => $file->getSize()) > 6 * 1024 * 1024) {
                        $fail('The total size of all attachments must not exceed 6 MB.');
                    }
                },
            ],
            'attachments.*' => 'file|mimes:pdf,jpg,png|max:2048',
            'is_half_day' => 'nullable|boolean',
            'duty_exchange_user_id' => 'nullable|exists:users,id',
        ], [
            'attachments.max' => 'You can upload a maximum of 3 attachments.',
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        if (! $leaveType->is_not_limited) {
            $request->validate([
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date',
            ]);
        }

        if ($leaveType->requires_attachment && ! $request->hasFile('attachments')) {
            return back()->with('error', __('flash.attachment_required'))->withInput();
        }

        if (! empty($validated['duty_exchange_user_id'])) {
            $candidates = get_duty_exchange_candidates(auth()->user(), auth()->id());
            $validCandidate = collect($candidates)->firstWhere('id', $validated['duty_exchange_user_id']);

            if (! $validCandidate) {
                return back()->with('error', __('flash.invalid_duty_exchange'))->withInput();
            }
        }

        $isHalfDay = $request->boolean('is_half_day');

        if ($leaveType->is_not_limited) {
            $totalDays = 0;
            $validated['start_date'] = $validated['start_date'] ?? now()->format('Y-m-d');
            $validated['end_date'] = null;
        } else {
            $totalDays = $this->leaveWorkflowService->calculateTotalDays(
                $validated['start_date'],
                $validated['end_date'],
                $isHalfDay
            );

            $totalDays = $totalDays == 0 ? 1 : $totalDays;

            if (
                ! $this->leaveWorkflowService->validateLeaveBalance(
                    auth()->user(),
                    $validated['leave_type_id'],
                    $totalDays
                )
            ) {
                return back()->with('error', __('flash.insufficient_balance'))->withInput();
            }

            if ($leaveType->per_leave_days && $totalDays > (int) $leaveType->per_leave_days) {
                return back()->with('error', __('flash.invalid_per_leave_days', ['days' => $leaveType->per_leave_days]))->withInput();
            }

            if (
                ! $this->leaveWorkflowService->validateNoOverlap(
                    auth()->user(),
                    $validated['start_date'],
                    $validated['end_date']
                )
            ) {
                return back()->with('error', __('flash.overlapping_leave'))->withInput();
            }
        }

        $leaveRequest = new LeaveRequest([
            'user_id' => auth()->id(),
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'staff_signature' => $validated['signature'],
            'status' => 'pending',
            'current_approval_level' => auth()->user()->isDepartmentHead() || auth()->user()->require_admin_approval ? 2 : 1,
            'duty_exchange_user_id' => $validated['duty_exchange_user_id'] ?? null,
            'is_half_day' => $isHalfDay,
        ]);

        if ($request->hasFile('attachments')) {
            $paths = [];
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('leave-attachments', 'public');
            }
            $leaveRequest->attachment_path = json_encode($paths);
        }

        $leaveRequest->save();

        if (auth()->user()->isDepartmentHead() || auth()->user()->require_admin_approval) {
            User::where('role', 'admin')
                ->whereIn('id', AdminAssignment::select('admin_id'))
                ->get()
                ->each(function ($admin) use ($leaveRequest) {
                    $admin->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'admin'));
                });
        } else {
            $departmentHeadId = $leaveRequest->user->department?->head_id;
            $departmentHead = $departmentHeadId ? User::find($departmentHeadId) : null;

            if ($departmentHead && $departmentHead->id !== $leaveRequest->user->id) {
                $departmentHead->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'department_head'));
            }

            if ($departmentHead && $departmentHead->id === $leaveRequest->user->id) {
                User::where('role', 'admin')
                    ->whereIn('id', AdminAssignment::select('admin_id'))
                    ->get()
                    ->each(function ($admin) use ($leaveRequest) {
                        $admin->notify(new LeaveRequestSubmittedNotification($leaveRequest, 'admin'));
                    });
            }
        }

        $leaveRequest->user->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'submitted'));

        return redirect()->route($this->routePrefix().'.index')
            ->with('success', __('flash.submit_success'));
    }

    public function edit(LeaveRequest $leaveRequest)
    {
        if (Gate::denies('update', $leaveRequest)) {
            abort(403);
        }

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $existingLeaves = auth()->user()->leaveRequests()
            ->where('id', '!=', $leaveRequest->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get(['start_date', 'end_date']);

        return view($this->viewPrefix().'.edit', compact('leaveRequest', 'leaveTypes', 'existingLeaves'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        if (Gate::denies('update', $leaveRequest)) {
            abort(403);
        }

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachments' => [
                'nullable',
                'array',
                'max:3',
                function ($attribute, $files, $fail) {
                    if (collect($files)->sum(fn ($file) => $file->getSize()) > 6 * 1024 * 1024) {
                        $fail('The total size of all attachments must not exceed 6 MB.');
                    }
                },
            ],
            'attachments.*' => 'file|mimes:pdf,jpg,png|max:2048',
            'is_half_day' => 'nullable|boolean',
            'remove_attachment' => 'nullable|boolean',
            'duty_exchange_user_id' => 'nullable|exists:users,id',
        ], [
            'attachments.max' => 'You can upload a maximum of 3 attachments.',
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        if (! $leaveType->is_not_limited) {
            $request->validate([
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
        }

        if ($leaveType->requires_attachment && ! $request->hasFile('attachments') && ! $request->boolean('remove_attachment') && $leaveRequest->attachment_path) {
            if ($request->boolean('remove_attachment') && ! $request->hasFile('attachments')) {
                return back()->with('error', __('flash.attachment_required'))->withInput();
            }
        } elseif ($leaveType->requires_attachment && ! $request->hasFile('attachments') && ! $leaveRequest->attachment_path) {
            return back()->with('error', __('flash.attachment_required'))->withInput();
        }

        if (! empty($validated['duty_exchange_user_id'])) {
            $candidates = get_duty_exchange_candidates(auth()->user(), auth()->id());
            $validCandidate = collect($candidates)->firstWhere('id', $validated['duty_exchange_user_id']);

            if (! $validCandidate) {
                return back()->with('error', __('flash.invalid_duty_exchange'))->withInput();
            }
        }

        $isHalfDay = $request->boolean('is_half_day');

        if ($leaveType->is_not_limited) {
            $totalDays = 0;
            $validated['start_date'] = $validated['start_date'] ?? now()->format('Y-m-d');
            $validated['end_date'] = null;
        } else {
            $totalDays = $this->leaveWorkflowService->calculateTotalDays(
                $validated['start_date'],
                $validated['end_date'],
                $isHalfDay
            );

            if (
                ! $this->leaveWorkflowService->validateLeaveBalance(
                    auth()->user(),
                    $validated['leave_type_id'],
                    $totalDays
                )
            ) {
                return back()->with('error', __('flash.insufficient_balance'))->withInput();
            }

            if ($leaveType->per_leave_days && $totalDays > (int) $leaveType->per_leave_days) {
                return back()->with('error', __('flash.invalid_per_leave_days', ['days' => $leaveType->per_leave_days]))->withInput();
            }

            if (
                ! $this->leaveWorkflowService->validateNoOverlap(
                    auth()->user(),
                    $validated['start_date'],
                    $validated['end_date'],
                    $leaveRequest->id
                )
            ) {
                return back()->with('error', __('flash.overlapping_leave'))->withInput();
            }
        }

        $leaveRequest->update([
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'is_half_day' => $isHalfDay,
            'duty_exchange_user_id' => $validated['duty_exchange_user_id'] ?? null,
        ]);

        if ($request->hasFile('attachments')) {
            if ($leaveRequest->attachment_path) {
                $existing = json_decode($leaveRequest->attachment_path, true) ?: [];
                foreach ($existing as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
            $paths = [];
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('leave-attachments', 'public');
            }
            $leaveRequest->update([
                'attachment_path' => json_encode($paths),
            ]);
        }

        if ($request->boolean('remove_attachment') && $leaveRequest->attachment_path) {
            $existing = json_decode($leaveRequest->attachment_path, true) ?: [];
            foreach ($existing as $path) {
                Storage::disk('public')->delete($path);
            }
            $leaveRequest->update(['attachment_path' => null]);
        }

        $departmentHead = $leaveRequest->user->department?->head;
        if ($departmentHead && $departmentHead->id !== $leaveRequest->user->id) {
            $departmentHead->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest, 'updated'));
        }

        return redirect()->route($this->routePrefix().'.show', $leaveRequest)
            ->with('success', __('flash.request_updated'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        if (Gate::denies('view', $leaveRequest)) {
            abort(403);
        }

        $leaveRequest->load('leaveType', 'reviewer', 'hr', 'super_admin', 'user.department');

        return view($this->viewPrefix().'.show', compact('leaveRequest'));
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        if (Gate::denies('update', $leaveRequest)) {
            abort(403);
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return back()->with('success', __('flash.cancel_success'));
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

        return redirect()->route($this->routePrefix().'.index')
            ->with('success', __('flash.request_deleted'));
    }
}
