@extends('layouts.app')

@section('title', __('department_head.leave_report'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('department_head.leave_report') }}</h2>
            <p class="cu-muted mt-1">{{ __('department_head.leave_report_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form id="department-head-leave-form" action="{{ route('department-head.reports.export') }}" method="POST"
            class="mt-4 flex flex-nowrap items-end gap-3 overflow-x-auto pb-2">
            @csrf
            <input type="date" name="start_date" id="dept_start_date" class="cu-input w-auto min-w-[150px]" placeholder="{{ __('common.start_date') }}">
            <input type="date" name="end_date" id="dept_end_date" class="cu-input w-auto min-w-[150px]" placeholder="{{ __('common.end_date') }}">

            <div class="flex gap-2 shrink-0">
                <button type="button" id="dept-leave-today-btn" class="cu-btn-secondary whitespace-nowrap">{{ __('common.today') }}</button>
                <button type="button" id="dept-leave-search-btn" class="cu-btn-primary whitespace-nowrap">{{ __('common.search') }}</button>
                <button type="submit" id="dept-leave-export-btn" class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_pdf') }}</button>
                <button type="submit" form="department-head-leave-form" formaction="{{ route('department-head.reports.export-xlsx') }}"
                    class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_xlsx') }}</button>
            </div>
        </form>
    </div>

    <div id="dept-leave-results" class="cu-card cu-card-body hidden">
        <div class="relative h-80 mb-6">
            <canvas id="deptLeaveBarChart" aria-label="{{ __('department_head.leave_report') }}"></canvas>
        </div>
        <h3 class="cu-section-title mb-4">{{ __('department_head.leave_report') }} {{ __('common.results') }}</h3>
        <div class="overflow-x-auto">
            <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.staff_id') }}</th>
                        <th>{{ __('common.leave_type') }}</th>
                        <th>{{ __('common.start_date') }}</th>
                        <th>{{ __('common.end_date') }}</th>
                        <th>{{ __('common.total_days') }}</th>
                        <th>{{ __('common.status') }}</th>
                    </tr>
                </thead>
                <tbody id="dept-leave-table-body">
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.getElementById('dept-leave-search-btn').addEventListener('click', function () {
        const startDate = document.getElementById('dept_start_date').value;
        const endDate = document.getElementById('dept_end_date').value;

        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        fetch(`{{ route('department-head.reports.leave-data') }}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
            .then(res => res.json())
            .then(data => {
                renderDeptLeaveResults(data);
            })
            .catch(err => {
                console.error(err);
                alert('{{ __('flash.error') }}');
            });
    });

    document.getElementById('dept-leave-today-btn').addEventListener('click', function () {
        const today = '{{ date('Y-m-d') }}';
        document.getElementById('dept_start_date').value = today;
        document.getElementById('dept_end_date').value = today;
        document.getElementById('dept-leave-search-btn').click();
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('dept-leave-search-btn').click();
    });

    function renderDeptLeaveResults(data) {
        const resultsDiv = document.getElementById('dept-leave-results');
        const tableBody = document.getElementById('dept-leave-table-body');
        const canvas = document.getElementById('deptLeaveBarChart');

        if (!data.table || !data.table.length) {
            resultsDiv.classList.add('hidden');
            return;
        }

        resultsDiv.classList.remove('hidden');
        tableBody.innerHTML = data.table.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td class="primary">${item.staff_name}</td>
                <td>${item.staff_id}</td>
                <td>${item.leave_type}</td>
                <td>${item.start_date}</td>
                <td>${item.end_date}</td>
                <td>${item.is_not_limited ? '-' : item.total_days}</td>
                <td>${item.status}</td>
            </tr>
        `).join('');

        if (window.deptLeaveChartInstance) {
            window.deptLeaveChartInstance.destroy();
        }

        window.deptLeaveChartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.chart.labels,
                datasets: [{
                    label: '{{ __('common.days') }}',
                    data: data.chart.values,
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    maxBarThickness: 48,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ` ${context.parsed.y} {{ __('common.days') }}`;
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
