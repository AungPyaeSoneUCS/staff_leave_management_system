@extends('layouts.app')

@section('title', __('nav.leave_types'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('nav.leave_types') }}</h2>
            <p class="cu-muted mt-1">{{ __('staff.leave_types_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        @if($leaveTypes->isEmpty())
            <p class="text-sm text-slate-500">{{ __('common.no_data') }}</p>
        @else
            <div class="space-y-3">
                @foreach($leaveTypes as $leaveType)
                    <a href="{{ route('staff.leave-types.show', $leaveType) }}"
                       class="block p-4 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors">
                        <div class="font-semibold text-slate-900">
                            {{ app()->getLocale() == 'my' ? ($leaveType->name_mm ?? $leaveType->name) : $leaveType->name }}
                        </div>
                        @if($leaveType->description)
                            <p class="text-sm text-slate-500 mt-1 line-clamp-2">
                                {{ app()->getLocale() == 'my' ? ($leaveType->description_mm ?? $leaveType->description) : $leaveType->description }}
                            </p>
                        @else
                            <p class="text-sm text-slate-400 mt-1">{{ __('common.no_description') }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
