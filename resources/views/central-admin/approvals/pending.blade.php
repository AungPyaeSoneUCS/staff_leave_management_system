@extends('layouts.app')

@section('title', __('central_admin.pending_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('central_admin.pending_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('central_admin.review_applications') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form method="GET" action="{{ route('central-admin.approvals.pending') }}" class="mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('central_admin.search_name_staff') }}" class="cu-input">
                </div>
                <div>
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
                    <button type="submit" class="cu-btn-secondary w-full">{{ __('common.filter') }}</button>
                </div>
            </div>
        </form>

        @foreach($pendingRequests as $request)
            @if($request->current_approval_level === 3 && auth()->user()->isSuperAdmin())
                <form id="approve-form-{{ $request->id }}" action="{{ route('central-admin.approvals.approve', $request) }}" method="POST" class="hidden">
                    @csrf
                </form>
                <form id="reject-form-{{ $request->id }}" action="{{ route('central-admin.approvals.reject', $request) }}" method="POST" class="hidden">
                    @csrf
                </form>
            @elseif($request->current_approval_level === 2 && auth()->user()->isAdmin() && ! auth()->user()->isSuperAdmin())
                <form id="approve-form-{{ $request->id }}" action="{{ route('central-admin.approvals.approve', $request) }}" method="POST" class="hidden">
                    @csrf
                </form>
                <form id="reject-form-{{ $request->id }}" action="{{ route('central-admin.approvals.reject', $request) }}" method="POST" class="hidden">
                    @csrf
                </form>
            @elseif($request->status === 'approved')
                <form id="revoke-form-{{ $request->id }}" action="{{ route('central-admin.approvals.revoke', $request) }}" method="POST" data-confirm="{{ __('common.confirm_revoke') }}" class="hidden">
                    @csrf
                </form>
            @endif
        @endforeach

        <form id="bulk-action-form" action="{{ route('central-admin.approvals.bulk') }}" method="POST">
            @csrf
            <div class="mb-4 flex flex-wrap items-center gap-4">
                <select name="bulk_action" class="cu-select" required>
                    <option value="">{{ __('common.bulk_action') }}</option>
                    <option value="approve">{{ __('common.approve') }}</option>
                    <option value="reject">{{ __('common.reject') }}</option>
                </select>
                @error('bulk_action')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
                <button type="submit" class="cu-btn-primary">{{ __('common.apply') }}</button>
            </div>

            @if($pendingRequests->isEmpty())
                <div class="text-center py-8">
                    <p class="text-slate-500">{{ __('central_admin.no_pending_requests') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
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
                                    <th>{{ __('common.actions') }}</th>
                                </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRequests as $request)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="selected[]" value="{{ $request->id }}" class="row-checkbox rounded border-slate-300 text-cu-600 focus:ring-cu-500" onclick="event.stopPropagation()">
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
                                            {{ app()->getLocale() == 'my' ? ($request->user->name_mm ?? $request->user->name) : $request->user->name }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ app()->getLocale() == 'my' ? ($request->leaveType->name_mm ?? $request->leaveType->name) : $request->leaveType->name }}
                                    </td>
                                    <td>
                                        {{ app()->getLocale() == 'my' ? ($request->user->position_mm ?? $request->user->position) : ($request->user->position ?: __('common.n_a')) }}
                                    </td>
                                    <td>{{ $request->user->department->name ?? __('common.no_department') }}</td>
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
                                        <span class="cu-badge-warning">{{ __('common.pending') }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('central-admin.approvals.show', $request) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.view') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pendingRequests->links() }}
                </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('select-all')?.addEventListener('change', function(e) {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = e.target.checked);
});
</script>
@endpush
@endsection
