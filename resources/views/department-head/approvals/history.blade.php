@extends('layouts.app')

@section('title', __('department_head.history_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('department_head.history_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('department_head.previously_processed') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form method="GET" action="{{ route('department-head.approvals.history') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="cu-label text-xs">{{ __('common.start_date') }}</label>
                <input type="date" name="start_date" value="{{ old('start_date', $filters['start_date'] ?? '') }}" class="cu-input w-auto min-w-[150px]">
            </div>
            <div>
                <label class="cu-label text-xs">{{ __('common.end_date') }}</label>
                <input type="date" name="end_date" value="{{ old('end_date', $filters['end_date'] ?? '') }}" class="cu-input w-auto min-w-[150px]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="cu-btn-primary whitespace-nowrap">{{ __('common.search') }}</button>
                <a href="{{ route('department-head.approvals.history') }}" class="cu-btn-secondary whitespace-nowrap">{{ __('common.reset') }}</a>
            </div>
        </form>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
            <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.leave_type') }}</th>
                        <th>{{ __('common.position') }}</th>
                        <th>{{ __('common.start_date') }}</th>
                        <th>{{ __('common.end_date') }}</th>
                        <th>{{ __('common.total_days') }}</th>
                        <th>{{ __('common.duty_exchange') }}</th>
                        <th>{{ __('common.attachment') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('department_head.reviewed_date') }}</th>
                        <th>{{ __('common.reviewed_by') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
            </thead>
            <tbody>
                @foreach($processedRequests as $request)
                    <tr>
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
                            {{ app()->getLocale() == 'my' ? ($request->user->position_mm ?? $request->user->position) : $request->user->position }}
                        </td>
                        <td>
                            {{ $request->start_date ? \App\Support\MyanmarDateFormatter::format($request->start_date, 'F d, Y') : __('common.n_a') }}
                        </td>
                        <td>
                            {{ $request->end_date ? \App\Support\MyanmarDateFormatter::format($request->end_date, 'F d, Y') : __('common.n_a') }}
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
                        <td>{{ app()->getLocale() == 'my' ? $request->reviewer->name_mm ?? $request->reviewer->name : $request->reviewer->name ?? ($request->hr->name ?? __('common.n_a')) }}</td>
                        <td>
                            <a href="{{ route('department-head.approvals.show', $request) }}" class="inline-flex items-center px-4 py-1.5 rounded-full text-sm text-amber-700 bg-amber-50 border border-amber-200 shadow-sm shadow-amber-600/10 transition-all duration-150 hover:bg-amber-100 hover:border-amber-300 hover:shadow-md" title="{{ __('common.view') }}">
                                {{ __('common.view') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $processedRequests->links() }}
    </div>
</div>
@endsection
