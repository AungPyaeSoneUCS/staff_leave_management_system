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
                    @foreach($departmentList as $department)
                        <option value="{{ $department->id }}" {{ (int) $department->id === (int) $departmentId ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'my' ? $department->name_mm ?? $department->name : $department->name }}
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
                        @php
                            $totalColumns = $dayColumns + 5;
                        @endphp
                        <thead>
                            <tr>
                                <th colspan="{{ $totalColumns }}" class="border border-slate-400 bg-slate-100 px-3 py-2.5 text-center text-base font-bold leading-relaxed">
                                    {{ $bookTitle }}
                                </th>
                            </tr>
                            <tr>
                                <th colspan="{{ $totalColumns }}" class="border border-slate-400 bg-slate-100 px-3 py-2.5 text-center text-sm font-bold">
                                    {{ app()->getLocale() == 'my' ? $department->name_mm ?? $department->name : $department->name }}
                                </th>
                            </tr>
                            <tr>
                                <th rowspan="2" class="border border-slate-400 bg-slate-100 px-3 py-2 text-center">{{ __('common.no') }}</th>
                                <th rowspan="2" class="border border-slate-400 bg-slate-100 px-3 py-2 text-left">{{ __('common.name') }}</th>
                                <th rowspan="2" class="border border-slate-400 bg-slate-100 px-3 py-2 text-center">{{ __('common.position') }}</th>
                                <th rowspan="2" class="border border-slate-400 bg-slate-100 px-3 py-2 text-left">{{ __('common.leave_type') }}</th>
                                <th colspan="{{ $dayColumns }}" class="border border-slate-400 bg-slate-100 px-3 py-2 text-center">{{ __('admin.leave_taking_date') }}</th>
                                <th rowspan="2" class="w-14 border border-slate-400 bg-slate-100 px-1 py-2 text-center">
                                    <form method="POST" action="{{ route('admin.leave-records.day-columns.add') }}">
                                        @csrf
                                        <button type="submit" title="{{ __('admin.leave_day_add_column') }}"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-300 bg-white text-lg font-bold leading-none text-slate-600 hover:border-sky-500 hover:bg-sky-50 hover:text-sky-700">&plus;</button>
                                    </form>
                                </th>
                            </tr>
                            <tr>
                                @foreach(range(1, $dayColumns) as $i)
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
                                    @foreach(range(1, $dayColumns) as $i)
                                        <td class="w-24 border border-slate-400 px-1 py-1 align-top">
                                            @include('admin.leave-records._day-cell', ['cell' => $templateRows[0]['dates'][$i - 1] ?? null, 'user' => $user, 'name' => $name, 'leaveType' => $templateRows[0]['type']])
                                        </td>
                                    @endforeach
                                </tr>
                                @foreach(array_slice($templateRows, 1) as $templateRow)
                                    <tr>
                                        <td class="border border-slate-400 px-3 py-1.5 text-center text-slate-700">{{ $templateRow['mm'] }}</td>
                                        @foreach(range(1, $dayColumns) as $i)
                                            <td class="w-24 border border-slate-400 px-1 py-1 align-top">
                                                @include('admin.leave-records._day-cell', ['cell' => $templateRow['dates'][$i - 1] ?? null, 'user' => $user, 'name' => $name, 'leaveType' => $templateRow['type']])
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="{{ $totalColumns }}" class="border border-slate-400 px-3 py-8 text-center text-slate-500">{{ __('admin.leave_records_empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <div id="leave-day-modal" class="hidden fixed inset-0 z-[900] flex items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 id="leave-day-modal-title" class="text-base font-semibold text-slate-900"></h3>
                <button type="button" onclick="closeLeaveDayModal()" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form id="leave-day-form" method="POST" action="{{ route('admin.leave-records.day.store') }}">
                @csrf
                <input type="hidden" name="_method" id="leave-day-method" value="">
                <input type="hidden" name="user_id" id="leave-day-user">
                <input type="hidden" name="leave_type_id" id="leave-day-type">
                <div class="space-y-4 px-5 py-5">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.name') }}</label>
                        <input type="text" id="leave-day-name-label" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700" readonly>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.leave_type') }}</label>
                        <input type="text" id="leave-day-type-label" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700" readonly>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.leave_day_start') }}</label>
                            <input type="date" name="start_date" id="leave-day-start" required onchange="recomputeLeaveDayTotal()"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.leave_day_end') }}</label>
                            <input type="date" name="end_date" id="leave-day-end" onchange="recomputeLeaveDayTotal()"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_half_day" value="1" id="leave-day-half" onchange="recomputeLeaveDayTotal()"
                            class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        {{ __('admin.leave_day_half') }}
                    </label>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.total_days') }}</label>
                        <input type="text" id="leave-day-total" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700" readonly>
                    </div>
                </div>
            </form>
            <div class="flex items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/80 px-5 py-4">
                <button type="button" id="leave-day-delete-btn"
                    class="cu-btn-danger hidden"
                    form="leave-day-delete-form"
                    data-confirm="{{ __('admin.leave_day_delete_confirm') }}">{{ __('common.delete') }}</button>
                <div class="ml-auto flex gap-2">
                    <button type="button" onclick="closeLeaveDayModal()" class="cu-btn-amber-nude">{{ __('common.cancel') }}</button>
                    <button type="submit" form="leave-day-form" class="cu-btn-primary">{{ __('common.save') }}</button>
                </div>
            </div>
        </div>
    </div>

    <form id="leave-day-delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script>
        function todayIso() {
            const pad = n => String(n).padStart(2, '0');
            const d = new Date();
            return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        }

        function toIsoDate(value) {
            if (!value) return '';
            const parts = value.split('/');
            if (parts.length !== 3) return '';
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }

        function recomputeLeaveDayTotal() {
            const startEl = document.getElementById('leave-day-start');
            const endEl = document.getElementById('leave-day-end');
            const halfEl = document.getElementById('leave-day-half');
            const totalEl = document.getElementById('leave-day-total');
            if (halfEl.checked) {
                endEl.value = '';
            }
            endEl.disabled = halfEl.checked;
            totalEl.value = '';
            if (!startEl.value) return;
            const params = new URLSearchParams({
                start_date: startEl.value,
                end_date: halfEl.checked ? '' : endEl.value,
                is_half_day: halfEl.checked ? '1' : '0',
            });
            fetch("{{ route('admin.leave-import.calculate-total-days') }}?" + params.toString())
                .then(response => response.json())
                .then(data => {
                    totalEl.value = data.total_days !== undefined && data.total_days !== null ? data.total_days : '';
                })
                .catch(() => {
                    totalEl.value = '';
                });
        }

        function openLeaveDayModal(button) {
            const isEdit = !!button.dataset.record;
            document.getElementById('leave-day-user').value = button.dataset.user;
            document.getElementById('leave-day-type').value = button.dataset.type;
            document.getElementById('leave-day-name-label').value = button.dataset.userName || '';
            document.getElementById('leave-day-type-label').value = button.dataset.typeName || '';
            document.getElementById('leave-day-method').value = isEdit ? 'PUT' : '';
            document.getElementById('leave-day-form').action = isEdit
                ? "{{ route('admin.leave-records.day.update', ['leaveRequest' => '__ID__']) }}".replace('__ID__', button.dataset.record)
                : "{{ route('admin.leave-records.day.store') }}";
            document.getElementById('leave-day-delete-form').action = "{{ route('admin.leave-records.day.destroy', ['leaveRequest' => '__ID__']) }}".replace('__ID__', button.dataset.record);
            document.getElementById('leave-day-modal-title').textContent = isEdit
                ? "{{ __('admin.edit_day') }}"
                : "{{ __('admin.add_day') }}";
            document.getElementById('leave-day-start').value = isEdit ? toIsoDate(button.dataset.start) : todayIso();
            document.getElementById('leave-day-end').value = isEdit ? toIsoDate(button.dataset.end) : '';
            document.getElementById('leave-day-half').checked = !!button.dataset.half;
            document.getElementById('leave-day-delete-btn').classList.toggle('hidden', !isEdit);
            document.getElementById('leave-day-modal').classList.remove('hidden');
            recomputeLeaveDayTotal();
        }

        function closeLeaveDayModal() {
            document.getElementById('leave-day-modal').classList.add('hidden');
        }

        document.addEventListener('click', function (event) {
            const button = event.target.closest('.day-cell, .day-cell-add');
            if (!button) return;
            event.preventDefault();
            openLeaveDayModal(button);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('leave-day-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            }
        });

        window.openLeaveDayModal = openLeaveDayModal;
        window.closeLeaveDayModal = closeLeaveDayModal;
        window.recomputeLeaveDayTotal = recomputeLeaveDayTotal;
    </script>
@endpush