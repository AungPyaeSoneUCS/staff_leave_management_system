@extends('layouts.app')

@section('title', __('admin.balance_report'))

@section('content')
    <div class="space-y-6">
        <div class="cu-card-header">
            <div>
                <h2 class="cu-page-title">{{ __('admin.balance_report') }}</h2>
                <p class="cu-muted mt-1">{{ __('admin.current_balances') }}</p>
            </div>
        </div>

        <div class="cu-card cu-card-body">
            <div class="cu-report-tile">
                <form id="balance-report-form" action="{{ route('admin.reports.export') }}" method="POST"
                    class="mt-4 flex flex-nowrap items-end gap-3 overflow-x-auto pb-2">
                    @csrf
                    <input type="hidden" name="type" value="balance">

                    <select name="department_id" id="balance_department_id" class="cu-select w-auto min-w-[150px]">
                        <option value="">{{ __('admin.all_departments') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ app()->getLocale() == 'my' ? $department->name_mm ?? $department->name : $department->name }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="staff_name" id="balance_staff_name" list="staff-suggestions"
                        class="cu-input w-auto min-w-[180px]"
                        placeholder="{{ __('common.search') }} {{ __('common.staff') }}..." autocomplete="off">
                    <datalist id="staff-suggestions"></datalist>

                    <select name="year" class="cu-select w-auto min-w-[100px]">
                        @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>

                    <select name="leave_type_id" class="cu-select w-auto min-w-[150px]">
                        <option value="">{{ __('admin.all_leave_types') }}</option>
                        @foreach($leaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}">{{ app()->getLocale() == 'my' ? $leaveType->name_mm ?? $leaveType->name : $leaveType->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex gap-2 shrink-0">
                        {{-- <button type="button" id="balance-today-btn" class="cu-btn-secondary whitespace-nowrap">{{  __('common.today') }}</button> --}}
                        <button type="button" id="balance-search-btn"
                            class="cu-btn-primary whitespace-nowrap">{{ __('common.search') }}</button>
                        <button type="submit" id="balance-export-btn"
                            class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_pdf') }}</button>
                        <button type="submit" form="balance-report-form" formaction="{{ route('admin.reports.export-xlsx') }}"
                            class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_xlsx') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="balance-results" class="cu-card cu-card-body hidden">
            <div class="overflow-x-auto mb-6">
                <div class="relative h-80" style="min-width: 600px;">
                    <canvas id="balanceBarChart" aria-label="{{ __('admin.balance_report') }}"></canvas>
                </div>
            </div>
            <h3 class="cu-section-title mb-4">{{ __('admin.balance_report') }} {{ __('common.results') }}</h3>
            <div class="overflow-x-auto">
                <table class="cu-table">
                    <thead>
                        <tr>
                            <th>{{ __('common.number') }}</th>
                            <th>{{ __('common.staff') }}</th>
                            <th>{{ __('common.department') }}</th>
                            <th>{{ __('common.leave_type') }}</th>
                            <th>{{ __('common.allocated_days') }}</th>
                            <th>{{ __('common.used_days') }}</th>
                            <th>{{ __('common.remaining_days') }}</th>
                        </tr>
                    </thead>
                    <tbody id="balance-table-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
            const staffSuggestions = <?php echo json_encode(\App\Models\User::where('role', 'staff')->orWhere('role', 'department_head')->get(['id', 'name', 'department_id']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

            document.getElementById('balance_staff_name').addEventListener('input', function () {
                const query = this.value.toLowerCase();
                const datalist = document.getElementById('staff-suggestions');
                datalist.innerHTML = '';

                if (query.length < 1) return;

                const matches = staffSuggestions.filter(s => s.name.toLowerCase().includes(query)).slice(0, 10);
                matches.forEach(s => {
                    const option = document.createElement('option');
                    option.value = s.name;
                    datalist.appendChild(option);
                });
            });

            document.getElementById('balance-search-btn').addEventListener('click', function () {
                const departmentId = document.getElementById('balance_department_id').value;
                const staffName = document.getElementById('balance_staff_name').value;
                const year = document.querySelector('select[name="year"]').value;
                const leaveTypeId = document.querySelector('select[name="leave_type_id"]').value;

                const params = new URLSearchParams();
                if (departmentId) params.append('department_id', departmentId);
                if (staffName) params.append('staff_name', staffName);
                if (year) params.append('year', year);
                if (leaveTypeId) params.append('leave_type_id', leaveTypeId);

                fetch(`{{ route('admin.reports.balance-data') }}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        renderBalanceResults(data);
                    })
                    .catch(err => {
                        console.error(err);
                        alert('{{ __('flash.error') }}');
                    });
            });

            // document.getElementById('balance-today-btn').addEventListener('click', function () {
            //     const today = '{{ date('Y-m-d') }}';
            //     document.getElementById('report_start_date').value = today;
            //     document.getElementById('report_end_date').value = today;
            //     document.getElementById('balance-search-btn').click();
            // });

            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('balance-search-btn').click();
            });

            ['balance_department_id', 'select[name="year"]', 'select[name="leave_type_id"]'].forEach(function (selector) {
                const el = selector.startsWith('select') ? document.querySelector(selector) : document.getElementById(selector);
                if (el) {
                    el.addEventListener('change', function () {
                        document.getElementById('balance-search-btn').click();
                    });
                }
            });

            function renderBalanceResults(data) {
                const resultsDiv = document.getElementById('balance-results');
                const tableBody = document.getElementById('balance-table-body');
                const canvas = document.getElementById('balanceBarChart');

                if (!data.length) {
                    resultsDiv.classList.add('hidden');
                    return;
                }

                resultsDiv.classList.remove('hidden');
                const grouped = {};
                data.forEach(item => {
                    const name = item.staff_name || '—';
                    if (!grouped[name]) {
                        grouped[name] = {
                            staff_name: name,
                            department: item.department,
                            leave_type: item.leave_type || '',
                            allocated_days: 0,
                            used_days: 0,
                            remaining_days: 0,
                            is_not_limited: item.is_not_limited,
                        };
                    }

                    if (!item.is_not_limited) {
                        grouped[name].allocated_days += Number(item.allocated_days_raw || 0);
                        grouped[name].used_days += Number(item.used_days_raw || 0);
                        grouped[name].remaining_days += Number(item.remaining_days_raw || 0);
                    }
                });

                const rows = Object.values(grouped);

                tableBody.innerHTML = data.map((item, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td class="primary">
                        <div class="flex items-center gap-2">
                            ${item.profile_image ? `<img src="${window.location.origin}/storage/${item.profile_image}" alt="" class="w-8 h-8 rounded-full object-cover">` : `<div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>`}
                            ${item.staff_name}
                        </div>
                    </td>
                    <td>${item.department}</td>
                    <td>${item.leave_type || ''}</td>
                    <td>${item.allocated_days}</td>
                    <td>${item.used_days}</td>
                    <td>${item.is_not_limited ? '-' : item.remaining_days}</td>
                </tr>
            `).join('');

                const usedTotal = rows.reduce((sum, item) => sum + (item.is_not_limited ? 0 : item.used_days), 0);
                const remainingTotal = rows.reduce((sum, item) => sum + (item.is_not_limited ? 0 : item.remaining_days), 0);

                if (window.balanceChartInstance) {
                    window.balanceChartInstance.destroy();
                }

                const chartCount = rows.length;
                const chartWrap = canvas.parentElement;
                chartWrap.style.minWidth = Math.max(600, chartCount * 90) + 'px';

                window.balanceChartInstance = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: rows.map(item => item.staff_name),
                        datasets: [
                            {
                                label: '{{ __('common.used_days') }}',
                                data: rows.map(item => item.is_not_limited ? 0 : item.used_days),
                                backgroundColor: '#ef4444',
                                borderRadius: 6,
                                maxBarThickness: 48,
                            },
                            {
                                label: '{{ __('common.remaining_days') }}',
                                data: rows.map(item => item.is_not_limited ? 0 : item.remaining_days),
                                backgroundColor: '#22c55e',
                                borderRadius: 6,
                                maxBarThickness: 48,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, padding: 14, font: { size: 12 } },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return ` ${context.dataset.label}: ${context.parsed.y} {{ __('common.days') }}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                ticks: { maxRotation: 45, minRotation: 0, font: { size: 11 } },
                                grid: { display: false },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, font: { size: 11 } },
                                grid: { color: 'rgba(148, 163, 184, 0.25)' },
                            },
                        },
                    },
                });
            }
        </script>
    @endpush
@endsection