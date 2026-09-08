@extends('layouts.app')

@section('title', __('admin.department_report'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.department_report') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.department_report_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form id="department-report-form" action="{{ route('admin.reports.export') }}" method="POST" class="mt-4 flex flex-nowrap items-end gap-3 overflow-x-auto pb-2">
            @csrf
            <input type="hidden" name="type" value="department">
            <input type="hidden" name="date_filter" id="report_date_filter" value="created_at">

            <input type="date" name="start_date" id="report_start_date" class="cu-input w-auto min-w-[150px]" placeholder="{{ __('common.start_date') }}">
            <input type="date" name="end_date" id="report_end_date" class="cu-input w-auto min-w-[150px]" placeholder="{{ __('common.end_date') }}">

            <div class="flex gap-2 shrink-0">
                <button type="button" id="department-today-btn" class="cu-btn-secondary whitespace-nowrap">{{ __('common.today') }}</button>
                <button type="button" id="department-search-btn" class="cu-btn-primary whitespace-nowrap">{{ __('common.search') }}</button>
                <button type="submit" id="department-export-btn" class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_pdf') }}</button>
                <button type="submit" form="department-report-form" formaction="{{ route('admin.reports.export-xlsx') }}"
                    class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_xlsx') }}</button>
            </div>
        </form>
    </div>

    <div id="department-results" class="cu-card cu-card-body hidden">
        <div class="relative h-96 mb-6">
            <canvas id="departmentStaffChart" aria-label="{{ __('common.staff_count') }}"></canvas>
        </div>
        <div class="relative h-96 mb-6">
            <canvas id="departmentDaysChart" aria-label="{{ __('common.total_days') }}"></canvas>
        </div>
        <h3 class="cu-section-title mb-4">{{ __('admin.department_report') }} {{ __('common.results') }}</h3>
        <div class="overflow-x-auto">
            <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.department') }}</th>
                        <th>{{ __('common.staff_count') }}</th>
                        <th>{{ __('common.total_days') }}</th>
                    </tr>
                </thead>
                <tbody id="department-table-body">
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.getElementById('department-search-btn').addEventListener('click', function () {
        const startDate = document.getElementById('report_start_date').value;
        const endDate = document.getElementById('report_end_date').value;

        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        params.append('date_filter', document.getElementById('report_date_filter').value);

        fetch(`{{ route('admin.reports.department-data') }}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
            .then(res => res.json())
            .then(data => {
                renderDepartmentResults(data);
            })
            .catch(err => {
                console.error(err);
                alert('{{ __('flash.error') }}');
            });
    });

    document.getElementById('department-today-btn').addEventListener('click', function () {
        const today = '{{ date('Y-m-d') }}';
        document.getElementById('report_start_date').value = today;
        document.getElementById('report_end_date').value = today;
        document.getElementById('report_date_filter').value = 'reviewed_at';
        document.getElementById('department-search-btn').click();
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('department-search-btn').click();
    });

    function renderDepartmentResults(data) {
        const resultsDiv = document.getElementById('department-results');
        const tableBody = document.getElementById('department-table-body');

        if (!data.table || !data.table.length) {
            resultsDiv.classList.add('hidden');
            return;
        }

        const hasAny = data.table.some(item => (Number(item.staff_count_raw ?? item.staff_count) > 0) || (Number(item.total_days_raw ?? item.total_days) > 0));

        if (!hasAny) {
            if (window.departmentStaffChartInstance) {
                window.departmentStaffChartInstance.destroy();
            }
            if (window.departmentDaysChartInstance) {
                window.departmentDaysChartInstance.destroy();
            }
            resultsDiv.classList.add('hidden');
            return;
        }

        resultsDiv.classList.remove('hidden');
        tableBody.innerHTML = data.table.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td class="primary">${item.department}</td>
                <td>${item.staff_count}</td>
                <td>${item.total_days}</td>
            </tr>
        `).join('');

        const labels = data.chart.labels;

        function buildChart(canvas, label, values, colors) {
            if (window[canvas.id + 'Instance']) {
                window[canvas.id + 'Instance'].destroy();
            }
            window[canvas.id + 'Instance'] = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: values,
                        backgroundColor: colors,
                        borderRadius: 6,
                        maxBarThickness: 48,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: label,
                            font: { size: 14, weight: 'bold' },
                        },
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return ` ${label}: ${context.parsed.y}`;
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

        buildChart(document.getElementById('departmentStaffChart'), '{{ __('common.staff_count') }}', data.chart.staff_values, data.chart.colors);
        buildChart(document.getElementById('departmentDaysChart'), '{{ __('common.total_days') }}', data.chart.values, data.chart.colors);
    }
</script>
@endpush
@endsection
