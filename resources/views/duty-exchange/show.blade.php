@extends('layouts.app')

@section('title', __('duty_exchange.detail_title'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <div class="flex justify-between items-start mb-6 gap-4">
            <div>
                <h2 class="cu-page-title">{{ __('duty_exchange.detail_title') }}</h2>
                <p class="cu-muted mt-1">{{ __('common.application_summary') }}</p>
            </div>
            <span class="cu-badge-warning">{{ __('duty_exchange.awaiting_confirmation') }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="cu-muted">{{ __('common.staff') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ app()->getLocale() == 'my' ? ($leaveRequest->user->name_mm ?? $leaveRequest->user->name) : $leaveRequest->user->name }}
                </p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.id_no') }}</p>
                <p class="text-base font-semibold text-slate-900">{{ $leaveRequest->user->staff_id ?? __('common.n_a') }}</p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.position') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ app()->getLocale() == 'my' ? ($leaveRequest->user->position_mm ?? $leaveRequest->user->position) : ($leaveRequest->user->position ?: __('common.n_a')) }}
                </p>
            </div>
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
                <p class="cu-muted">{{ __('common.submitted') }}</p>
                <p class="text-base font-semibold text-slate-900">{{\App\Support\MyanmarDateFormatter::diffForHumans($leaveRequest->created_at)}}</p>
            </div>
        </div>

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

        @if($leaveRequest->duty_exchange_user_id && $leaveRequest->dutyExchangeUser)
            <div class="border-t border-slate-100 pt-6 mb-6">
                <h3 class="cu-section-title mb-4">{{ __('common.duty_exchange_staff') }}</h3>
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

        @if($leaveRequest->isAwaitingDutyExchange())
            <div class="border-t border-slate-100 pt-6">
                <h3 class="cu-section-title mb-4">{{ __('duty_exchange.decision_panel') }}</h3>
                <div class="flex flex-col sm:flex-row gap-4">
                    <form action="{{ route('duty-exchange.accept', $leaveRequest) }}" method="POST" class="flex-1">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('duty_exchange.accept') }}</label>
                            <input type="text" name="remarks"
                                   placeholder="{{ __('duty_exchange.remarks_optional') }}"
                                   class="cu-input">
                            @error('remarks')
                                <p class="cu-form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="cu-btn-success w-full">{{ __('duty_exchange.accept_and_forward') }}</button>
                    </form>

                    <form action="{{ route('duty-exchange.reject', $leaveRequest) }}" method="POST" class="flex-1">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('duty_exchange.reject') }}</label>
                            <input type="text" name="remarks"
                                   placeholder="{{ __('duty_exchange.remarks_required') }}"
                                   required
                                   class="cu-input">
                            @error('remarks')
                                <p class="cu-form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="cu-btn-danger w-full">{{ __('duty_exchange.reject_request') }}</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="border-t border-slate-100 pt-6 mt-6">
            <a href="{{ route('duty-exchange.index') }}" class="cu-link">
                {{ __('common.back') }}
            </a>
        </div>
    </div>
</div>
@endsection