@props(['title', 'value', 'color' => 'cu', 'icon' => null])

@php
    $valueClass = match ($color) {
        'green' => 'cu-stat-value-green',
        'yellow', 'amber' => 'cu-stat-value-yellow',
        'red' => 'cu-stat-value-red',
        'blue', 'sky' => 'cu-stat-value-blue',
        'indigo', 'teal', 'cu' => 'cu-stat-value-cu',
        default => 'cu-stat-value-cu',
    };

    $iconBg = match ($color) {
        'green' => 'bg-emerald-50 text-emerald-600',
        'yellow', 'amber' => 'bg-amber-50 text-amber-600',
        'red' => 'bg-red-50 text-red-600',
        'blue', 'sky' => 'bg-sky-50 text-sky-600',
        'indigo', 'teal', 'cu' => 'bg-primary-50 text-primary-600',
        default => 'bg-primary-50 text-primary-600',
    };
@endphp

<div class="cu-stat-card animate-fade-in-up hover:-translate-y-1 transition-transform duration-200">
    <div class="flex items-center">
        @if($icon)
            <div class="flex-shrink-0 mr-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $iconBg }} animate-pulse-slow">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $icon !!}
                    </svg>
                </div>
            </div>
        @endif
        <div class="w-0 flex-1">
            <dl>
                <dt class="cu-stat-label py-2">{{ $title }}</dt>
                <dd class="cu-stat-value {{ $valueClass }}">{{ $value }}</dd>
            </dl>
        </div>
    </div>
</div>
