@extends('layouts.app')

@section('title', __('department-head.staff_details'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('department-head.staff_details') }}</h2>
            <p class="cu-muted mt-1">{{ __('department-head.staff_details_subtitle') }}</p>
        </div>
        <a href="{{ route('department-head.staff.index') }}" class="cu-btn-secondary">{{ __('common.back') }}</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="cu-card cu-card-body">
            <div class="flex flex-col items-center text-center">
                @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                @else
                    <div class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center border-4 border-white shadow-lg">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                @endif
                <h3 class="mt-4 text-lg font-semibold text-slate-900">
                    {{ app()->getLocale() == 'my' ? $user->name_mm ?? $user->name : $user->name }}
                </h3>
                <p class="text-sm text-slate-500">{{ $user->position ?? __('common.n_a') }}</p>
                <p class="text-sm text-slate-500">{{ $user->department?->name ?? __('common.no_department') }}</p>
                <span @class([
                    'cu-badge-success mt-2' => $user->is_active,
                    'cu-badge-danger mt-2' => ! $user->is_active,
                ])>
                    {{ $user->is_active ? __('common.staff.active') : __('common.staff.inactive') }}
                </span>
            </div>
            <div class="mt-6 pt-6 border-t border-slate-100 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ __('common.staff_id') }}</span>
                    <span class="font-medium text-slate-900">{{ $user->staff_id ?? __('common.n_a') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ __('common.email_address') }}</span>
                    <span class="font-medium text-slate-900">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ __('common.phone') }}</span>
                    <span class="font-medium text-slate-900">{{ my_phone($user->phone) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ __('common.department') }}</span>
                    <span class="font-medium text-slate-900">{{ $user->department?->name ?? __('common.n_a') }}</span>
                </div>
            </div>
        </div>

        <div class="cu-card cu-card-body">
            <h3 class="cu-section-title mb-4">{{ __('common.leave_balances') }}</h3>
            @if($balances->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($balances as $balance)
                        <div class="cu-balance-tile">
                            <p class="text-sm text-slate-500">
                                {{ app()->getLocale() == 'my' ? $balance->leaveType->name_mm ?? $balance->leaveType->name : $balance->leaveType->name }}
                            </p>
                            <p class="text-2xl font-bold text-primary-600 mt-1">
                                {{ $balance->leaveType->is_not_limited ? '-' : my_number($balance->remaining_days) }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                @if($balance->leaveType->is_not_limited)
                                    {{ __('common.unlimited') }}
                                @else
                                    {{ __('common.out_of_days', ['days' => my_number($balance->allocated_days)]) }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">{{ __('common.no_data') }}</p>
            @endif
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <h3 class="cu-section-title mb-4">{{ __('common.recent_leave_requests') }}</h3>
        @if($user->leaveRequests->count() > 0)
            <div class="overflow-x-auto -mx-2">
                <table class="cu-table">
                    <thead>
                        <tr>
                            <th>{{ __('common.number') }}</th>
                            <th>{{ __('common.leave_type') }}</th>
                            <th>{{ __('common.start_date') }}</th>
                            <th>{{ __('common.end_date') }}</th>
                            <th>{{ __('common.total_days') }}</th>
                            <th>{{ __('common.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->leaveRequests->take(10) as $request)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="primary">
                                    {{ app()->getLocale() == 'my' ? ($request->leaveType->name_mm ?? $request->leaveType->name) : $request->leaveType->name }}
                                </td>
                                <td>
                                    {{ $request->start_date ? \App\Support\MyanmarDateFormatter::format($request->start_date, 'F d, Y') : __('common.n_a') }}
                                </td>
                                <td>
                                    {{ $request->end_date ? \App\Support\MyanmarDateFormatter::format($request->end_date, 'F d, Y') : __('common.unlimited') }}
                                </td>
                                <td>{{ $request->leaveType->is_not_limited ? '-' : ($request->is_half_day ? __('common.half_day') : my_number($request->total_days)) }}</td>
                                <td>
                                    <span @class([
                                        'cu-badge-success' => $request->status === 'approved',
                                        'cu-badge-warning' => $request->status === 'pending',
                                        'cu-badge-danger' => in_array($request->status, ['rejected', 'revoked']),
                                        'cu-badge-neutral' => ! in_array($request->status, ['approved', 'pending', 'rejected', 'revoked']),
                                    ])>
                                        {{ __('common.' . $request->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-slate-500">{{ __('common.no_data') }}</p>
        @endif
    </div>
</div>
@endsection
