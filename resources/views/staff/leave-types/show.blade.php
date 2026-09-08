@extends('layouts.app')

@section('title', app()->getLocale() == 'my' ? ($leaveType->name_mm ?? $leaveType->name) : $leaveType->name)

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="cu-card cu-card-body">
            <div class="flex justify-between items-start mb-6 gap-4">
                <div>
                    <h2 class="cu-page-title">
                        {{ app()->getLocale() == 'my' ? ($leaveType->name_mm ?? $leaveType->name) : $leaveType->name }}
                    </h2>
                    <p class="cu-muted mt-1">{{ $leaveType->code ?? __('common.n_a') }}</p>
                </div>
                <span @class([
                    'cu-badge-success' => $leaveType->is_active,
                    'cu-badge-danger' => !$leaveType->is_active,
                ])>
                    {{ $leaveType->is_active ? __('common.active') : __('common.inactive') }}
                </span>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t border-slate-100 pt-6">
                    <div>
                        <p class="cu-muted">{{ __('common.code') }}</p>
                        <p class="text-base font-semibold text-slate-900 mt-1">{{ $leaveType->code ?? __('common.n_a') }}
                        </p>
                    </div>
                    <div>
                        <p class="cu-muted">{{ __('common.annual_allocation') }}</p>
                        <p class="text-base font-semibold text-slate-900 mt-1">
                            {{ $leaveType->is_not_limited ? __('common.unlimited') : my_number($leaveType->annual_allocation) }}
                        </p>
                    </div>
                    <div>
                        <p class="cu-muted">{{ __('common.per_leave_days') }}</p>
                        <p class="text-base font-semibold text-slate-900 mt-1">
                            {{ $leaveType->per_leave_days ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="cu-muted">{{ __('common.requires_attachment') }}</p>
                        <p class="text-base font-semibold text-slate-900 mt-1">
                            {{ $leaveType->requires_attachment ? __('common.required') : __('common.optional') }}
                        </p>
                    </div>
                    <div>
                        <p class="cu-muted">{{ __('common.carry_forward_limit') }}</p>
                        <p class="text-base font-semibold text-slate-900 mt-1">
                            {{ $leaveType->carry_forward_limit ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="cu-muted">{{ __('common.is_not_limited') }}</p>
                        <p class="text-base font-semibold text-slate-900 mt-1">
                            {{ $leaveType->is_not_limited ? __('common.yes') : __('common.no') }}
                        </p>
                    </div>
                </div>
                @if($leaveType->description)
                    <div>
                        <p class="cu-muted">{{ __('common.description') }}</p>
                        <p class="text-base text-slate-800 mt-1 leading-6 break-words whitespace-pre-wrap text-justify">
                            {{ app()->getLocale() == 'my' ? ($leaveType->description_mm ?? $leaveType->description) : $leaveType->description }}
                        </p>
                    </div>
                @else
                    <div>
                        <p class="cu-muted">{{ __('common.description') }}</p>
                        <p class="text-sm text-slate-400 mt-1">{{ __('common.no_description') }}</p>
                    </div>
                @endif
            </div>

            {{-- <div class="border-t border-slate-100 pt-6 mt-6">
                <a href="{{ route('staff.leave-types.index') }}" class="cu-link">
                    {{ __('staff.back_to_leave_types') }}
                </a>
            </div> --}}
        </div>
    </div>
@endsection
