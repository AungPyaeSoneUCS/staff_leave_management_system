@extends('layouts.app')

@section('title', __('admin.leave_records'))

@section('content')
    <div class="space-y-6">
        <div class="cu-card-header">
            <div>
                <h2 class="cu-page-title">{{ __('admin.leave_records') }}</h2>
                <p class="cu-muted mt-1">{{ __('admin.leave_records_subtitle') }}</p>
            </div>
            <a href="{{ route('admin.leave-records.order') }}" class="cu-btn-secondary whitespace-nowrap">
                {{ __('admin.custom_staff_order') }}
            </a>
        </div>

        <div class="cu-card cu-card-body">
            <form method="GET" action="{{ route('admin.leave-records') }}"
                class="mt-4 flex flex-nowrap items-end gap-3 overflow-x-auto pb-2">
                <select name="year" id="leave-record-year" class="cu-select w-auto min-w-[110px]" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ (int) $y === (int) $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>

                <select name="department_id" id="leave-record-department" class="cu-select w-auto min-w-[180px]" onchange="this.form.submit()">
                    <option value="">{{ __('admin.all_departments') }}</option>
                    @foreach($books as $book)
                        <option value="{{ $book['department']->id }}" {{ (int) $book['department']->id === (int) $departmentId ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'my' ? $book['department']->name_mm ?? $book['department']->name : $book['department']->name }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('admin.leave-records.export', ['year' => $year, 'department_id' => $departmentId]) }}"
                    class="cu-btn-secondary whitespace-nowrap">{{ __('common.export_xlsx') }}</a>
            </form>
        </div>

        @foreach($books as $book)
            @php $department = $book['department']; $staff = $book['staff']; @endphp
            <div class="cu-card cu-card-body">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm" style="min-width: 1400px;">
                        <thead>
                            <tr>
                                <th colspan="14" class="border border-slate-400 bg-slate-100 px-3 py-2.5 text-center text-base font-bold leading-relaxed">
                                    {{ $bookTitle }}
                                </th>
                            </tr>
                            <tr>
                                <th colspan="14" class="border border-slate-400 bg-slate-100 px-3 py-2.5 text-center text-sm font-bold">
                                    {{ app()->getLocale() == 'my' ? $department->name_mm ?? $department->name : $department->name }}
                                </th>
                            </tr>
                            <tr>
                                <th rowspan="2" class="border border-slate-400 bg-slate-100 px-3 py-2 text-center">{{ __('common.no') }}</th>
                                <th rowspan="2" class="border border-slate-400 bg-slate-100 px-3 py-2 text-left">{{ __('common.name') }}</th>
                                <th rowspan="2" class="border border-slate-400 bg-slate-100 px-3 py-2 text-center">{{ __('common.position') }}</th>
                                <th rowspan="2" class="border border-slate-400 bg-slate-100 px-3 py-2 text-left">{{ __('common.leave_type') }}</th>
                                <th colspan="10" class="border border-slate-400 bg-slate-100 px-3 py-2 text-center">{{ __('admin.leave_taking_date') }}</th>
                            </tr>
                            <tr>
                                @foreach(range(1, 10) as $i)
                                    <th class="w-24 border border-slate-400 bg-slate-100 px-1 py-1.5 text-center text-xs">{{ $i }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff as $staffIndex => $entry)
                                @php
                                    $user = $entry['user'];
                                    $name = $user->name_mm ?? $user->name;
                                    $position = $user->position_mm ?? ($user->position ?? '');
                                    $templateRows = $entry['rows'];
                                @endphp
                                <tr>
                                    <td rowspan="6" class="border border-slate-400 px-3 py-1.5 text-center align-middle text-slate-700">{{ $staffIndex + 1 }}</td>
                                    <td rowspan="6" class="border border-slate-400 px-3 py-1.5 align-middle font-medium text-slate-900">{{ $name }}</td>
                                    <td rowspan="6" class="border border-slate-400 px-3 py-1.5 text-center align-middle text-slate-700">{{ $position }}</td>
                                    <td class="border border-slate-400 px-3 py-1.5 text-center text-slate-700">{{ $templateRows[0]['mm'] }}</td>
                                    @foreach(range(1, 10) as $i)
                                        <td class="w-24 border border-slate-400 px-1 py-1.5 text-center text-xs whitespace-nowrap text-slate-700">{{ $templateRows[0]['dates'][$i - 1] ?? '' }}</td>
                                    @endforeach
                                </tr>
                                @foreach(array_slice($templateRows, 1) as $templateRow)
                                    <tr>
                                        <td class="border border-slate-400 px-3 py-1.5 text-center text-slate-700">{{ $templateRow['mm'] }}</td>
                                        @foreach(range(1, 10) as $i)
                                            <td class="w-24 border border-slate-400 px-1 py-1.5 text-center text-xs whitespace-nowrap text-slate-700">{{ $templateRow['dates'][$i - 1] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="14" class="border border-slate-400 px-3 py-8 text-center text-slate-500">{{ __('admin.leave_records_empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection