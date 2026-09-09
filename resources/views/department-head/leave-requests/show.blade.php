@extends('layouts.app')

@section('title', __('staff.leave_request_details'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <div class="flex justify-between items-start mb-6 gap-4">
            <div>
                <h2 class="cu-page-title">{{ __('staff.leave_request_details') }}</h2>
                <p class="cu-muted mt-1">{{ __('common.application_summary') }}</p>
            </div>
            <span @class([
                'cu-badge-success' => $leaveRequest->status === 'approved',
                'cu-badge-warning' => $leaveRequest->status === 'pending',
                'cu-badge-danger' => in_array($leaveRequest->status, ['rejected', 'revoked']),
                'cu-badge-neutral' => ! in_array($leaveRequest->status, ['approved', 'pending', 'rejected', 'revoked']),
            ])>
                {{ __('common.' . $leaveRequest->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="cu-muted">{{ __('common.leave_type') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ app()->getLocale() == 'my' ? ($leaveRequest->leaveType->name_mm ?? $leaveRequest->leaveType->name) : $leaveRequest->leaveType->name }}
                </p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.total_days') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ $leaveRequest->leaveType->is_not_limited ? '-' : ($leaveRequest->is_half_day ? __('common.half_day') : my_number($leaveRequest->total_days) . ' ' . __('common.days')) }}
                </p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.start_date') }}</p>
                <p class="text-base font-semibold text-slate-900">{{\App\Support\MyanmarDateFormatter::format($leaveRequest->start_date, 'l, F d, Y')}}</p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.end_date') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ $leaveRequest->end_date ? \App\Support\MyanmarDateFormatter::format($leaveRequest->end_date, 'l, F d, Y') : __('common.unlimited') }}
                </p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.requires_attachment') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ $leaveRequest->leaveType->requires_attachment ? __('common.required') : __('common.optional') }}
                </p>
            </div>
        </div>

        @if($leaveRequest->leaveType->requires_attachment && ! $leaveRequest->attachment_path)
            <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4">
                <p class="text-sm text-red-700">{{ __('common.attachment_missing') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                <p class="cu-muted">{{ __('common.reason') }}</p>
                <p class="text-base text-slate-800 mt-1">{{ $leaveRequest->reason }}</p>
            </div>
            @if($leaveRequest->staff_signature)
                <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                    <p class="cu-muted">{{ __('common.applicant_signature') }}</p>
                    <img src="{{ $leaveRequest->staff_signature }}" alt="{{ __('common.signature') }}"
                         class="mt-1 w-36 h-20 object-contain border border-slate-200 rounded-lg bg-white p-1">
                </div>
            @endif
        </div>

        @if($leaveRequest->attachment_path)
            @php $attachments = json_decode($leaveRequest->attachment_path, true) ?: [$leaveRequest->attachment_path]; @endphp
            <div class="mb-6">
                <p class="cu-muted">{{ __('common.attachment') }}</p>
                <div class="mt-1 space-y-1">
                    @foreach($attachments as $path)
                        <a href="{{ Storage::url($path) }}" target="_blank" class="cu-link inline-flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            {{ __('common.view_document') }} {{ $loop->iteration }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($leaveRequest->reviewed_at || $leaveRequest->isCancelled())
            <div class="border-t border-slate-100 pt-6">
                <h3 class="cu-section-title mb-4">{{ __('common.review_details') }}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                                <th class="px-4 py-3 font-medium">{{ __('common.reviewed_by') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('common.position') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('common.reviewed_date') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('common.remarks') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('common.signature') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if($leaveRequest->reviewer)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-semibold text-slate-900">
                                            {{ app()->getLocale() == 'my' ? ($leaveRequest->reviewer->name_mm ?? $leaveRequest->reviewer->name) : $leaveRequest->reviewer->name }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-800">{{ __('common.department_head') }}</td>
                                    <td class="px-4 py-3 align-top text-slate-800">
                                        {{ \App\Support\MyanmarDateFormatter::format($leaveRequest->reviewed_at, 'F d, Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-800">{{ $leaveRequest->reviewer_remarks ?: ($leaveRequest->review_remarks ?: '—') }}</td>
                                    <td class="px-4 py-3 align-top">
                                        @if($leaveRequest->reviewer_signature)
                                            <img src="{{ $leaveRequest->reviewer_signature }}" alt="{{ __('common.signature') }}"
                                                 class="w-36 h-20 object-contain border border-slate-200 rounded-lg bg-white p-1">
                                        @else
                                            <span class="text-slate-800">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @if($leaveRequest->hr)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-semibold text-slate-900">
                                            {{ app()->getLocale() == 'my' ? ($leaveRequest->hr->name_mm ?? $leaveRequest->hr->name) : $leaveRequest->hr->name }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-800">{{ __('common.hr_central_admin') }}</td>
                                    <td class="px-4 py-3 align-top text-slate-800">
                                        {{ \App\Support\MyanmarDateFormatter::format($leaveRequest->reviewed_at, 'F d, Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-800">{{ $leaveRequest->hr_remarks ?: '—' }}</td>
                                    <td class="px-4 py-3 align-top">
                                        @if($leaveRequest->hr_signature)
                                            <img src="{{ $leaveRequest->hr_signature }}" alt="{{ __('common.signature') }}"
                                                 class="w-36 h-20 object-contain border border-slate-200 rounded-lg bg-white p-1">
                                        @else
                                            <span class="text-slate-800">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @if($leaveRequest->super_admin)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-semibold text-slate-900">
                                            {{ app()->getLocale() == 'my' ? ($leaveRequest->super_admin->name_mm ?? $leaveRequest->super_admin->name) : $leaveRequest->super_admin->name }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-800">{{ __('common.super_admin') }}</td>
                                    <td class="px-4 py-3 align-top text-slate-800">
                                        {{ \App\Support\MyanmarDateFormatter::format($leaveRequest->reviewed_at, 'F d, Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-800">{{ $leaveRequest->super_admin_remarks ?: '—' }}</td>
                                    <td class="px-4 py-3 align-top">
                                        @if($leaveRequest->super_admin_signature)
                                            <img src="{{ $leaveRequest->super_admin_signature }}" alt="{{ __('common.signature') }}"
                                                 class="w-36 h-20 object-contain border border-slate-200 rounded-lg bg-white p-1">
                                        @else
                                            <span class="text-slate-800">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($leaveRequest->review_remarks && ! $leaveRequest->reviewer && ! $leaveRequest->hr && ! $leaveRequest->super_admin)
                    <div class="mt-4">
                        <p class="cu-muted">{{ __('common.remarks') }}</p>
                        <p class="text-base text-slate-800 mt-1">{{ $leaveRequest->review_remarks }}</p>
                    </div>
                @endif
            </div>
        @endif

        @if($leaveRequest->duty_exchange_user_id && $leaveRequest->dutyExchangeUser)
            <div class="border-t border-slate-100 pt-6">
                <h3 class="cu-section-title mb-4">{{ __('common.duty_exchange') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="cu-muted">{{ __('common.staff') }}</p>
                        <p class="text-base font-semibold text-slate-900">
                            {{ app()->getLocale() == 'my' ? ($leaveRequest->dutyExchangeUser->name_mm ?? $leaveRequest->dutyExchangeUser->name) : $leaveRequest->dutyExchangeUser->name }}
                        </p>
                    </div>
                    <div>
                        <p class="cu-muted">{{ __('common.position') }}</p>
                        <p class="text-base font-semibold text-slate-900">{{ $leaveRequest->dutyExchangeUser->position ?? '-' }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($leaveRequest->isPending())
            <div class="border-t border-slate-100 pt-6 flex justify-end">
                <form action="{{ route('department-head.leave-requests.cancel', $leaveRequest) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="cu-btn-amber-nude"
                            data-confirm="{{ __('staff.are_you_sure_cancel') }}">
                        {{ __('staff.cancel_request') }}
                    </button>
                </form>
            </div>
        @endif

        <div class="border-t border-slate-100 pt-6 mt-6">
            <a href="{{ route('department-head.leave-requests.index') }}" class="cu-link">
                {{ __('staff.back_to_requests') }}
            </a>
        </div>
    </div>
</div>
@endsection
