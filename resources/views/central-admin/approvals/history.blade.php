@extends('layouts.app')

@section('title', __('central_admin.history_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('central_admin.history_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('central_admin.processed_applications') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form method="GET" action="{{ route('central-admin.approvals.history') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="cu-label text-xs">{{ __('central_admin.staff_label') }}</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('central_admin.search_name_staff') }}" list="history-staff-suggestions" class="cu-input">
                    <datalist id="history-staff-suggestions">
                        @foreach(\App\Models\User::whereIn('role', ['staff', 'department_head'])->orderBy('name')->get(['name', 'name_mm', 'staff_id']) as $staffer)
                            <option value="{{ app()->getLocale() == 'my' ? ($staffer->name_mm ?? $staffer->name) : $staffer->name }}"></option>
                            <option value="{{ $staffer->staff_id }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="cu-label text-xs">{{ __('common.leave_type') }}</label>
                    <select name="leave_type_id" class="cu-select">
                        <option value="">{{ __('common.all_leave_types') }}</option>
                        @foreach(\App\Models\LeaveType::where('is_active', true)->get() as $type)
                            <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'my' ? $type->name_mm ?? $type->name : $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="cu-label text-xs">{{ __('common.start_date') }}</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="cu-input">
                </div>
                <div>
                    <label class="cu-label text-xs">{{ __('common.end_date') }}</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="cu-input">
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="submit" class="cu-btn-secondary">{{ __('common.filter') }}</button>
                <a href="{{ route('central-admin.approvals.history') }}" class="cu-btn-amber-nude">{{ __('common.reset') }}</a>
            </div>
        </form>
    </div>

    <form id="history-bulk-form" action="{{ route('central-admin.approvals.bulk-destroy') }}" method="POST">
        @csrf
        <div class="mb-3 flex justify-end">
            <button type="submit" class="cu-btn-danger-nude"
                    data-confirm="{{ __('central_admin.confirm_bulk_delete') }}">{{ __('common.delete_selected') }}</button>
        </div>

        <div class="cu-table-wrap overflow-x-auto">
            <table class="cu-table">
                <thead>
                    <tr>
                        <th width="30" class="text-center">
                            <input type="checkbox" id="select-all" class="rounded border-slate-300 text-cu-600 focus:ring-cu-500">
                        </th>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.leave_type') }}</th>
                        <th>{{ __('common.position') }}</th>
                        <th>{{ __('common.department') }}</th>
                        <th>{{ __('common.start_date') }}</th>
                        <th>{{ __('common.end_date') }}</th>
                        <th>{{ __('common.total_days') }}</th>
                        <th>{{ __('common.duty_exchange') }}</th>
                        <th>{{ __('common.attachment') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('department_head.reviewed_date') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($processedRequests as $request)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="selected[]" value="{{ $request->id }}" class="row-checkbox rounded border-slate-300 text-cu-600 focus:ring-cu-500">
                        </td>
                        <td>{{ $loop->iteration }}</td>
                        <td class="primary">
                            <div class="flex items-center gap-2">
                                @if($request->user->profile_image)
                                    <img src="{{ asset('storage/' . $request->user->profile_image) }}" alt="{{ $request->user->name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                {{ app()->getLocale() == 'my' ? $request->user->name_mm ?? $request->user->name : $request->user->name }}
                            </div>
                        </td>
                        <td>
                            {{ app()->getLocale() == 'my' ? ($request->leaveType->name_mm ?? $request->leaveType->name) : $request->leaveType->name }}
                        </td>
                        <td>
                            {{ app()->getLocale() == 'my' ? ($request->user->position_mm ?? $request->user->position) : ($request->user->position ?: __('common.n_a')) }}
                        </td>
                        <td>
                            {{ $request->user->department ? (app()->getLocale() == 'my' ? ($request->user->department->name_mm ?? $request->user->department->name) : $request->user->department->name) : __('common.n_a') }}
                        </td>
                        <td>
                            {{\App\Support\MyanmarDateFormatter::format($request->start_date, 'F d, Y')}}
                        </td>
                        <td>
                            {{ $request->end_date ? \App\Support\MyanmarDateFormatter::format($request->end_date, 'F d, Y') : __('common.unlimited') }}
                        </td>
                        <td>{{ $request->leaveType->is_not_limited ? '-' : ($request->is_half_day ? __('common.half_day') : my_number($request->total_days)) }}</td>
                        <td>
                            @if($request->duty_exchange_user_id && $request->dutyExchangeUser)
                                {{ app()->getLocale() == 'my' ? ($request->dutyExchangeUser->name_mm ?? $request->dutyExchangeUser->name) : $request->dutyExchangeUser->name }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td>
                            @if($request->attachment_path)
                                <span class="cu-badge-success">{{ __('common.yes') }}</span>
                            @else
                                <span class="cu-badge-neutral">{{ __('common.no') }}</span>
                            @endif
                        </td>
                        <td>
                            <span @class([
                                'cu-badge-success' => $request->status === 'approved',
                                'cu-badge-danger' => in_array($request->status, ['rejected', 'revoked']),
                                'cu-badge-neutral' => ! in_array($request->status, ['approved', 'rejected', 'revoked']),
                            ])>
                                {{ __('common.' . $request->status) }}
                            </span>
                        </td>
                        <td>{{ $request->reviewed_at ? \App\Support\MyanmarDateFormatter::format($request->reviewed_at, 'F d, Y') : __('common.n_a') }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('central-admin.approvals.show', $request) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.view') }}</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $processedRequests->links() }}
    </div>
</form>
</div>

@push('scripts')
<script>
document.getElementById('select-all')?.addEventListener('change', function(e) {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = e.target.checked);
});
</script>
@endpush
@endsection
