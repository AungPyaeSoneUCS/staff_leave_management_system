@extends('layouts.app')

@section('title', __('admin.holidays'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.holidays') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.holidays_subtitle') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.holidays.calendar') }}" class="cu-btn-primary">
                {{ __('admin.calendar_view') }}
            </a>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form method="GET" action="{{ route('admin.holidays.index') }}" class="mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('common.search') }}..." class="cu-input">
                </div>
                <div>
                    <input type="number" name="year" value="{{ request('year') }}" placeholder="{{ __('common.year') }}..." class="cu-input">
                </div>
                <div>
                    <button type="submit" class="cu-btn-secondary w-full">{{ __('common.filter') }}</button>
                </div>
            </div>
        </form>

        @if($holidays->isEmpty())
            <div class="text-center py-8">
                <p class="text-slate-500">{{ __('admin.no_holidays') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.date') }}</th>
                        <th>{{ __('common.description') }}</th>
                        <th>{{ __('admin.recurring') }}</th>
                        <th>{{ __('admin.default_holiday') }}</th>
                        <th>{{ __('admin.replaces_holiday') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                    <tbody>
                        @foreach($holidays as $holiday)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="primary">{{ app()->getLocale() == 'my' ? ($holiday->name_mm ?? $holiday->name) : $holiday->name }}</td>
                                <td>{{\App\Support\MyanmarDateFormatter::format($holiday->date, 'F d, Y')}}</td>
                                <td>{{ $holiday->description ?: '—' }}</td>
                                <td>
                                    @if($holiday->is_recurring)
                                        <span class="cu-badge-success">{{ __('admin.yes') }}</span>
                                    @else
                                        <span class="cu-badge-neutral">{{ __('admin.no') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($holiday->is_default)
                                        <span class="cu-badge-success">{{ __('admin.yes') }}</span>
                                    @else
                                        <span class="cu-badge-neutral">{{ __('admin.no') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($holiday->replacedHoliday)
                                        <span class="text-sm text-slate-600">{{ app()->getLocale() == 'my' ? ($holiday->replacedHoliday->name_mm ?? $holiday->replacedHoliday->name) : $holiday->replacedHoliday->name }}</span>
                                        @if($holiday->replacement_note)
                                            <p class="text-xs text-slate-500">{{ $holiday->replacement_note }}</p>
                                        @endif
                                    @else
                                        <span class="text-sm text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.holidays.edit', $holiday) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.edit') }}</a>
                                    <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" class="inline" data-confirm="{{ __('admin.delete_holiday_confirm') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cu-btn-danger !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $holidays->links() }}
            </div>
        @endif
    </div>
</div>
@endsection