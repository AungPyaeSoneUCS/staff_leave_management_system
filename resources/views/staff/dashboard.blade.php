@extends('layouts.app')

@section('title', __('staff.dashboard_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('staff.dashboard_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('staff.dashboard_subtitle') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-dashboard-widget
            title="{{ __('staff.pending_requests') }}"
            :value="$pendingCount !== null ? my_number($pendingCount) : '0'"
            color="yellow"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/>" />

        <x-dashboard-widget
            title="{{ __('staff.approved_this_year') }}"
            :value="my_number($recentRequests->where('status', 'approved')->count())"
            color="green"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/>" />

    </div>
    <div class="cu-card cu-card-body">
        <h3 class="cu-section-title mb-6">{{ __('common.leave_balances') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($leaveBalances as $balance)
                <div class="leave-tile cu-balance-tile p-6 text-center bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-base font-semibold text-slate-700">{{ app()->getLocale() == 'my' ? ($balance['leave_type_mm'] ?? $balance['leave_type']) : $balance['leave_type'] }}</p>
                    <p class="text-4xl font-extrabold text-primary-600 py-2">
                        {{ $balance['is_not_limited'] ? "-" : my_number($balance['remaining_days']) }}
                    </p>
                    <p class="text-sm text-slate-500 mt-1">
                        @if($balance['is_not_limited'])
                            {{ __('common.unlimited') }}
                        @else
                            {{ __('common.out_of_days', ['days' => my_number($balance['allocated_days'])]) }}
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection