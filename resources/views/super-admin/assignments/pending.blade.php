@extends('layouts.app')

@section('title', __('super_admin.pending_assignments_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('super_admin.pending_assignments_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('super_admin.pending_assignments_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
            <thead>
                <tr>
                    <th>{{ __('super_admin.leave_request_id') }}</th>
                    <th>{{ __('common.staff_name') }}</th>
                    <th>{{ __('common.leave_type') }}</th>
                    <th>{{ __('common.days') }}</th>
                    <th>{{ __('common.start_date') }}</th>
                    <th>{{ __('common.end_date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingLevel2 as $leaveRequest)
                    <tr>
                        <td>#{{ $leaveRequest->id }}</td>
                        <td class="primary">
                            <div class="flex items-center gap-2">
                                @if($leaveRequest->user->profile_image)
                                    <img src="{{ asset('storage/' . $leaveRequest->user->profile_image) }}" alt="{{ $leaveRequest->user->name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                {{ app()->getLocale() == 'my' ? ($leaveRequest->user->name_mm ?? $leaveRequest->user->name) : $leaveRequest->user->name }}
                            </div>
                        </td>
                        <td>
                            {{ app()->getLocale() == 'my' ? ($leaveRequest->leaveType->name_mm ?? $leaveRequest->leaveType->name) : $leaveRequest->leaveType->name }}
                        </td>
                        <td>{{ $leaveRequest->leaveType->is_not_limited ? '-' : ($leaveRequest->is_half_day ? __('common.half_day') : my_number($leaveRequest->total_days)) }}</td>
                        <td>{{ \App\Support\MyanmarDateFormatter::format($leaveRequest->start_date, 'F d, Y') }}</td>
                        <td>{{ $leaveRequest->end_date ? \App\Support\MyanmarDateFormatter::format($leaveRequest->end_date, 'F d, Y') : __('common.unlimited') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-500">{{ __('super_admin.no_pending_level2') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pendingLevel2->links() }}
    </div>
</div>
@endsection
