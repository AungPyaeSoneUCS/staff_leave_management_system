@extends('layouts.app')

@section('title', __('admin.leave_summary_report'))

@section('content')
    <div class="space-y-6">
        <div class="cu-card-header">
            <div>
                <h2 class="cu-page-title">{{ __('admin.leave_summary_report') }}</h2>
                <p class="cu-muted mt-1">{{ __('admin.overview_filters') }}</p>
            </div>
        </div>

        <div class="cu-card cu-card-body">
            <div class="cu-report-tile">
                <form id="leave-summary-form" action="{{ route('admin.reports.export') }}" method="POST"
                    class="w-full mt-4 flex flex-nowrap items-end gap-3 overflow-x-auto pb-2">
                    @csrf
                    <input type="hidden" name="type" value="leave_summary">
                    <input type="hidden" name="date_filter" id="summary_date_filter" value="leave_period">

                    <input type="text" name="department_name" id="summary_department_name" list="summary-department-suggestions"
                        class="cu-input w-auto min-w-[160px]"
                        placeholder="{{ __('common.department') }}..." autocomplete="off">
                    <datalist id="summary-department-suggestions"></datalist>

                    <input type="text" name="staff_name" id="summary_staff_name" list="summary-staff-suggestions"
                        class="cu-input w-auto min-w-[180px]"
                        placeholder="{{ __('common.search') }} {{ __('common.staff') }}..." autocomplete="off">
                    <datalist id="summary-staff-suggestions"></datalist>

                    <select name="status" id="summary_status" class="cu-select w-auto min-w-[150px]">
                        <option value="">{{ __('admin.all_statuses') }}</option>
                        @foreach(['approved', 'rejected', 'cancelled', 'revoked'] as $status)
                            <option value="{{ $status }}">{{ __('common.'.$status) }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="start_date" id="summary_start_date" class="cu-input w-auto min-w-[150px]"
                        placeholder="{{ __('common.start_date') }}">
                    <input type="date" name="end_date" id="summary_end_date" class="cu-input w-auto min-w-[150px]"
                        placeholder="{{ __('common.end_date') }}">

                    <div class="flex gap-2 shrink-0">
                        <button type="button" id="summary-today-btn" class="cu-btn-secondary whitespace-nowrap">{{ __('common.today') }}</button>
                        <button type="button" id="summary-search-btn"
                            class="cu-btn-primary whitespace-nowrap">{{ __('common.search') }}</button>
                        <button type="submit" id="summary-export-btn"
                            class="cu-btn-secondary whitespace-nowrap">{{ __('common.export_pdf') }}</button>
                        <button type="submit" form="leave-summary-form" formaction="{{ route('admin.reports.export-xlsx') }}"
                            class="cu-btn-secondary whitespace-nowrap">{{ __('common.export_xlsx') }}</button>
                    </div>
                </form>
            </div>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <div class="cu-card">
                <div class="cu-card-body">
                    <h3 class="cu-section-title mb-1">{{ __('admin.purge_history') }}</h3>
                    <p class="cu-muted mb-4">{{ __('admin.purge_history_hint') }}</p>
                    <form action="{{ route('admin.reports.purge-history') }}" method="POST" class="flex flex-wrap items-center gap-3">
                        @csrf
                        <input type="hidden" name="leave_type_id" id="purge_leave_type_id">
                        <input type="text" id="purge_leave_type_name" list="purge-leave-type-list" class="cu-input w-auto min-w-[180px]" placeholder="{{ __('common.leave_type') }}" autocomplete="off">
                        <datalist id="purge-leave-type-list">
                            @foreach(\App\Models\LeaveType::where('is_active', true)->orderBy('name')->get() as $type)
                                <option value="{{ app()->getLocale() == 'my' ? $type->name_mm ?? $type->name : $type->name }}" data-id="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </datalist>

                        <input type="text" name="year" id="purge_year" list="purge-year-list" class="cu-input w-auto min-w-[120px]" placeholder="{{ __('common.year') }}" autocomplete="off">
                        <datalist id="purge-year-list">
                            @for($y = date('Y')-1; $y >= date('Y')-5; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </datalist>

                        <button type="submit" class="cu-btn-danger-nude whitespace-nowrap" data-confirm="{{ __('admin.confirm_purge_history') }}" data-confirm-text="{{ __('admin.purge_confirm_action') }}">{{ __('admin.purge_history') }}</button>
                    </form>
                </div>
            </div>
        @endif

        <div id="summary-results" class="cu-card cu-card-body hidden">
            <div class="relative h-80 mb-6">
                <canvas id="summaryBarChart"></canvas>
            </div>
            <h3 class="cu-section-title mb-4">{{ __('admin.leave_summary_report') }} {{ __('common.results') }}</h3>
            <div class="overflow-x-auto">
                <table class="cu-table">
                    <thead>
                        <tr>
                            <th>{{ __('common.number') }}</th>
                            <th>{{ __('common.staff') }}</th>
                            <th>{{ __('common.staff_id') }}</th>
                            <th>{{ __('common.department') }}</th>
                            <th>{{ __('common.leave_type') }}</th>
                            <th>{{ __('common.start_date') }}</th>
                            <th>{{ __('common.end_date') }}</th>
                            <th>{{ __('common.total_days') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th>{{ __('common.duty_exchange') }}</th>
                        </tr>
                    </thead>
                    <tbody id="summary-table-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
            const appLocale = '{{ app()->getLocale() }}';
            const summaryStaffSuggestions = <?php echo json_encode(\App\Models\User::where('role', 'staff')->orWhere('role', 'department_head')->get(['id', 'name', 'name_mm']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const summaryDepartmentSuggestions = <?php echo json_encode($departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'name_mm' => $d->name_mm])->values()->all(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const purgeLeaveTypes = <?php echo json_encode(\App\Models\LeaveType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'name_mm'])->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'name_mm' => $t->name_mm])->values()->all(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

            const purgeTypeInput = document.getElementById('purge_leave_type_name');
            const purgeTypeId = document.getElementById('purge_leave_type_id');
            const purgeTypeList = document.getElementById('purge-leave-type-list');

            function renderPurgeTypeSuggestions(query) {
                purgeTypeList.innerHTML = '';
                const q = query.toLowerCase();
                const matches = purgeLeaveTypes
                    .filter(t => t.name.toLowerCase().includes(q) || (t.name_mm || '').toLowerCase().includes(q));
                matches.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = appLocale === 'my' ? (t.name_mm || t.name) : t.name;
                    opt.dataset.id = t.id;
                    purgeTypeList.appendChild(opt);
                });
            }

            purgeTypeInput.addEventListener('input', function () {
                const val = this.value;
                const match = purgeLeaveTypes.find(t =>
                    (appLocale === 'my' ? (t.name_mm || t.name) : t.name) === val
                );
                purgeTypeId.value = match ? match.id : '';
                renderPurgeTypeSuggestions(val);
            });

            purgeTypeInput.addEventListener('change', function () {
                const val = this.value;
                const match = purgeLeaveTypes.find(t =>
                    (appLocale === 'my' ? (t.name_mm || t.name) : t.name) === val
                );
                purgeTypeId.value = match ? match.id : '';
                document.getElementById('summary-search-btn').click();
            });

            renderPurgeTypeSuggestions('');

            purgeTypeInput.addEventListener('focus', function () {
                renderPurgeTypeSuggestions(this.value);
            });

            const purgeYearInput = document.querySelector('[name="year"]');
            const purgeYearList = document.getElementById('purge-year-list');
            const years = <?php echo json_encode(range(date('Y')-1, date('Y')-5)); ?>;

            function renderPurgeYearSuggestions(query) {
                purgeYearList.innerHTML = '';
                const q = query.toLowerCase();
                const matches = years.filter(y => String(y).includes(q));
                matches.forEach(y => {
                    const opt = document.createElement('option');
                    opt.value = y;
                    purgeYearList.appendChild(opt);
                });
            }

            purgeYearInput.addEventListener('input', function () {
                renderPurgeYearSuggestions(this.value);
            });

            renderPurgeYearSuggestions('');

            purgeYearInput.addEventListener('focus', function () {
                renderPurgeYearSuggestions(this.value);
            });

            purgeYearInput.addEventListener('change', function () {
                document.getElementById('summary-search-btn').click();
            });

            document.getElementById('summary_department_name').addEventListener('input', function () {
                const query = this.value.toLowerCase();
                const datalist = document.getElementById('summary-department-suggestions');
                datalist.innerHTML = '';

                const matches = query.length < 1
                    ? summaryDepartmentSuggestions
                    : summaryDepartmentSuggestions
                        .filter(d => d.name.toLowerCase().includes(query) || (d.name_mm || '').toLowerCase().includes(query))
                        .slice(0, 10);
                matches.forEach(d => {
                    const option = document.createElement('option');
                    option.value = appLocale === 'my' ? (d.name_mm || d.name) : d.name;
                    datalist.appendChild(option);
                });
            });

            (function () {
                const dl = document.getElementById('summary-department-suggestions');
                summaryDepartmentSuggestions.forEach(d => {
                    const option = document.createElement('option');
                    option.value = appLocale === 'my' ? (d.name_mm || d.name) : d.name;
                    dl.appendChild(option);
                });
            })();

            document.getElementById('summary_department_name').addEventListener('focus', function () {
                const datalist = document.getElementById('summary-department-suggestions');
                if (!datalist.children.length) {
                    summaryDepartmentSuggestions.forEach(d => {
                        const option = document.createElement('option');
                        option.value = appLocale === 'my' ? (d.name_mm || d.name) : d.name;
                        datalist.appendChild(option);
                    });
                }
            });

            document.getElementById('summary_staff_name').addEventListener('input', function () {
                const query = this.value.toLowerCase();
                const datalist = document.getElementById('summary-staff-suggestions');
                datalist.innerHTML = '';

                if (query.length < 1) return;

                const matches = summaryStaffSuggestions
                    .filter(s => s.name.toLowerCase().includes(query) || (s.name_mm || '').toLowerCase().includes(query))
                    .slice(0, 10);
                matches.forEach(s => {
                    const option = document.createElement('option');
                    option.value = s.name;
                    datalist.appendChild(option);
                });
            });

            document.getElementById('summary_staff_name').addEventListener('focus', function () {
                const datalist = document.getElementById('summary-staff-suggestions');
                if (!datalist.children.length) {
                    summaryStaffSuggestions.slice(0, 10).forEach(s => {
                        const option = document.createElement('option');
                        option.value = s.name;
                        datalist.appendChild(option);
                    });
                }
            });

            document.getElementById('summary-search-btn').addEventListener('click', function () {
                const departmentName = document.getElementById('summary_department_name').value;
                const staffName = document.getElementById('summary_staff_name').value;
                const status = document.getElementById('summary_status').value;
                const startDate = document.getElementById('summary_start_date').value;
                const endDate = document.getElementById('summary_end_date').value;
                const dateFilter = document.getElementById('summary_date_filter').value;

                const params = new URLSearchParams();
                if (departmentName) params.append('department_name', departmentName);
                if (staffName) params.append('staff_name', staffName);
                if (status) params.append('status', status);
                if (startDate) params.append('start_date', startDate);
                if (endDate) params.append('end_date', endDate);
                const purgeTypeId = document.getElementById('purge_leave_type_id').value;
                const purgeYear = document.getElementById('purge_year').value;
                if (purgeTypeId) params.append('leave_type_id', purgeTypeId);
                if (purgeYear) params.append('year', purgeYear);
                params.append('date_filter', dateFilter);

                fetch(`{{ route('admin.reports.leave-summary-data') }}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        renderSummaryResults(data);
                    })
                    .catch(err => {
                        console.error(err);
                        alert('{{ __('flash.error') }}');
                    });
            });

            document.getElementById('summary-today-btn').addEventListener('click', function () {
                const today = '{{ date('Y-m-d') }}';
                document.getElementById('summary_start_date').value = today;
                document.getElementById('summary_end_date').value = today;
                document.getElementById('summary_date_filter').value = 'reviewed_at';
                document.getElementById('summary-search-btn').click();
            });

            ['summary_start_date', 'summary_end_date'].forEach(function (id) {
                document.getElementById(id).addEventListener('change', function () {
                    document.getElementById('summary_date_filter').value = 'leave_period';
                });
            });

            ['summary_status'].forEach(function (id) {
                document.getElementById(id).addEventListener('change', function () {
                    document.getElementById('summary-search-btn').click();
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('summary-search-btn').click();
            });

            function renderSummaryResults(data) {
                const resultsDiv = document.getElementById('summary-results');
                const tableBody = document.getElementById('summary-table-body');
                const canvas = document.getElementById('summaryBarChart');

                if (!data.table || !data.table.length) {
                    resultsDiv.classList.add('hidden');
                    return;
                }

                resultsDiv.classList.remove('hidden');

                tableBody.innerHTML = data.table.map((item, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td class="primary">
                        <div class="flex items-center gap-2">
                            ${item.profile_image ? `<img src="${window.location.origin}/storage/${item.profile_image}" alt="" class="w-8 h-8 rounded-full object-cover">` : `<div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>`}
                            ${item.staff_name}
                        </div>
                    </td>
                    <td>${item.staff_id}</td>
                    <td>${item.department}</td>
                    <td>${item.leave_type}</td>
                    <td>${item.start_date}</td>
                    <td>${item.end_date}</td>
                    <td>${item.is_not_limited ? '-' : item.total_days}</td>
                    <td>${item.status}</td>
                    <td>${item.duty_exchange}</td>
                </tr>
            `).join('');

                if (window.summaryChartInstance) {
                    window.summaryChartInstance.destroy();
                }

                const labels = data.chart.labels;
                const values = data.chart.values;

                window.summaryChartInstance = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '{{ __('common.total_days') }}',
                            data: values,
                            backgroundColor: '#3b82f6',
                            borderWidth: 1,
                            barPercentage: 0.5,
                            categoryPercentage: 0.7,
                            maxBarThickness: 40
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
@endsection
