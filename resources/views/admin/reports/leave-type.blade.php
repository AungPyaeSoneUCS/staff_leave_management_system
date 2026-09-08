@extends('layouts.app')

@section('title', __('admin.leave_type_report'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.leave_type_report') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.leave_type_report_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form id="leave-type-report-form" action="{{ route('admin.reports.export') }}" method="POST" class="mt-4 flex flex-nowrap items-end gap-3 overflow-x-auto pb-2">
            @csrf
            <input type="hidden" name="type" value="leave_type">
            <input type="hidden" name="date_filter" id="leave_type_date_filter" value="created_at">

            <select name="department_id" id="leave_type_department_id" class="cu-select w-auto min-w-[150px]">
                <option value="">{{ __('admin.all_departments') }}</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ app()->getLocale() == 'my' ? $department->name_mm ?? $department->name : $department->name }}</option>
                @endforeach
            </select>

            <input type="date" name="start_date" id="leave_type_start_date" class="cu-input w-auto min-w-[150px]" placeholder="{{ __('common.start_date') }}">
            <input type="date" name="end_date" id="leave_type_end_date" class="cu-input w-auto min-w-[150px]" placeholder="{{ __('common.end_date') }}">

            <div class="flex gap-2 shrink-0">
                <button type="button" id="leave-type-today-btn" class="cu-btn-secondary whitespace-nowrap">{{ __('common.today') }}</button>
                <button type="button" id="leave-type-search-btn" class="cu-btn-primary whitespace-nowrap">{{ __('common.search') }}</button>
                <button type="submit" id="leave-type-export-btn" class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_pdf') }}</button>
                <button type="submit" form="leave-type-report-form" formaction="{{ route('admin.reports.export-xlsx') }}"
                    class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_xlsx') }}</button>
            </div>
        </form>
    </div>

    <div id="leave-type-results" class="cu-card cu-card-body hidden">
        <div class="relative h-80 mb-6">
            <canvas id="leaveTypeBarChart" aria-label="{{ __('admin.leave_by_type') }}"></canvas>
        </div>
        <h3 class="cu-section-title mb-4">{{ __('admin.leave_type_report') }} {{ __('common.results') }}</h3>
        <div class="overflow-x-auto">
            <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.leave_type') }}</th>
                        <th>{{ __('common.staff_count') }}</th>
                    </tr>
                </thead>
                <tbody id="leave-type-table-body">
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.getElementById('leave-type-search-btn').addEventListener('click', function () {
        const departmentId = document.getElementById('leave_type_department_id').value;
        const startDate = document.getElementById('leave_type_start_date').value;
        const endDate = document.getElementById('leave_type_end_date').value;

        const params = new URLSearchParams();
        if (departmentId) params.append('department_id', departmentId);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        params.append('date_filter', document.getElementById('leave_type_date_filter').value);

        fetch(`{{ route('admin.reports.leave-type-data') }}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
            .then(res => res.json())
            .then(data => {
                renderLeaveTypeResults(data);
            })
            .catch(err => {
                console.error(err);
                alert('{{ __('flash.error') }}');
            });
    });

    document.getElementById('leave-type-today-btn').addEventListener('click', function () {
        const today = '{{ date('Y-m-d') }}';
        document.getElementById('leave_type_start_date').value = today;
        document.getElementById('leave_type_end_date').value = today;
        document.getElementById('leave_type_date_filter').value = 'reviewed_at';
        document.getElementById('leave-type-search-btn').click();
    });

    document.getElementById('leave_type_department_id').addEventListener('change', function () {
        document.getElementById('leave-type-search-btn').click();
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('leave-type-search-btn').click();
    });

    function renderLeaveTypeResults(data) {
        const resultsDiv = document.getElementById('leave-type-results');
        const tableBody = document.getElementById('leave-type-table-body');
        const canvas = document.getElementById('leaveTypeBarChart');

        if (!data.table || !data.table.length) {
            resultsDiv.classList.add('hidden');
            return;
        }

        const hasAny = data.table.some(item => Number(item.staff_count_raw ?? item.staff_count) > 0);

        if (!hasAny) {
            if (window.leaveTypeChartInstance) {
                window.leaveTypeChartInstance.destroy();
            }
            resultsDiv.classList.add('hidden');
            return;
        }

        resultsDiv.classList.remove('hidden');
        tableBody.innerHTML = data.table.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td class="primary">${item.leave_type}</td>
                <td>${item.staff_count}</td>
            </tr>
        `).join('');

        if (window.leaveTypeChartInstance) {
            window.leaveTypeChartInstance.destroy();
        }

        window.leaveTypeChartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.chart.labels,
                datasets: [{
                    label: '{{ __('common.staff_count') }}',
                    data: data.chart.values,
                    backgroundColor: data.chart.colors,
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
                                return ` ${context.parsed.y} {{ __('common.staff_count') }}`;
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
