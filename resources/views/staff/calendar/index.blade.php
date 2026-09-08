@extends('layouts.app')

@section('title', __('staff.calendar'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('staff.calendar') }}</h2>
            <p class="cu-muted mt-1">{{ __('staff.calendar_subtitle') }}</p>
        </div>
        <form method="GET" action="{{ route('staff.calendar') }}" class="flex items-center gap-2">
            <select name="month" class="cu-select" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{\App\Support\MyanmarDateFormatter::format(\Carbon\Carbon::create()->month($m), 'F')}}
                    </option>
                @endfor
            </select>
            <select name="year" class="cu-select" onchange="this.form.submit()">
                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </form>
    </div>

    <div class="cu-card cu-card-body">
        <div class="flex items-center justify-between mb-6">
            <button onclick="changeMonth(-1)" class="cu-btn-secondary">
                &larr; {{ __('common.previous') }}
            </button>
            <h3 class="text-lg font-semibold text-slate-900">
                {{\App\Support\MyanmarDateFormatter::format(\Carbon\Carbon::create($year, $month, 1), 'F Y')}}
            </h3>
            <button onclick="changeMonth(1)" class="cu-btn-secondary">
                {{ __('common.next') }} &rarr;
            </button>
        </div>

        <div class="grid grid-cols-7 gap-px bg-slate-200 rounded-xl overflow-hidden">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="bg-slate-50 p-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    {{ $day }}
                </div>
            @endforeach

            @php
                $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
                $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
                $startDayOfWeek = $startOfMonth->dayOfWeek;
                $daysInMonth = $endOfMonth->day;
            @endphp

            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="bg-slate-50/50 p-3 min-h-[100px]"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $currentDate = \Carbon\Carbon::create($year, $month, $day);
                    $dateKey = $currentDate->format('Y-m-d');
                    $requests = $calendar[$dateKey]['requests'] ?? collect();
                    $isWeekend = $currentDate->isWeekend();
                    $isToday = $dateKey === $today;
                @endphp
                <div class="bg-white p-2 min-h-[100px] {{ $isWeekend ? 'bg-slate-50' : '' }} {{ $isToday ? 'ring-2 ring-primary-500 ring-inset' : '' }} hover:bg-slate-50 transition-colors">
                    <div class="text-sm font-medium {{ $isToday ? 'text-primary-600' : ($isWeekend ? 'text-slate-500' : 'text-slate-700') }}">
                        {{ $day }}
                    </div>
                    @php
                        $dayHoliday = $calendar[$dateKey]['holiday'] ?? null;
                    @endphp
                    @if($dayHoliday)
                        <div class="mt-1">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium truncate bg-red-50 text-red-700 border border-red-200 w-full">
                                {{ app()->getLocale() == 'my' ? ($dayHoliday->name_mm ?? $dayHoliday->name) : $dayHoliday->name }}
                            </span>
                        </div>
                    @endif
                    @foreach($requests as $request)
                        <div class="mt-1">
                            <span @class([
                                'inline-block px-1.5 py-0.5 rounded text-[10px] font-medium truncate',
                                'bg-amber-100 text-amber-800' => $request->status === 'pending',
                                'bg-emerald-100 text-emerald-800' => $request->status === 'approved',
                                'bg-rose-100 text-rose-800' => in_array($request->status, ['rejected', 'revoked']),
                            ])>
                                {{ $request->leaveType->code ?? 'LV' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>
</div>

@push('scripts')
<script>
function changeMonth(delta) {
    const url = new URL(window.location.href);
    const month = parseInt(url.searchParams.get('month')) || {{ $month }};
    const year = parseInt(url.searchParams.get('year')) || {{ $year }};
    const date = new Date(year, month - 1 + delta, 1);
    url.searchParams.set('month', date.getMonth() + 1);
    url.searchParams.set('year', date.getFullYear());
    window.location.href = url.toString();
}
</script>
@endpush
@endsection