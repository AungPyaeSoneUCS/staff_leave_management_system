@extends('layouts.app')

@section('title', __('admin.daily_report'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.daily_report') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.daily_report_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form id="daily-report-form" action="{{ route('admin.reports.export') }}" method="POST" class="mt-4 flex flex-nowrap items-end gap-3 overflow-x-auto pb-2">
            @csrf
            <input type="hidden" name="type" value="daily">
            <input type="hidden" name="start_date" value="{{ now()->format('Y-m-d') }}">
            <input type="hidden" name="end_date" value="{{ now()->format('Y-m-d') }}">

            <div class="flex gap-2 shrink-0">
                <button type="submit" id="daily-export-btn" class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_pdf') }}</button>
                <button type="submit" form="daily-report-form" formaction="{{ route('admin.reports.export-xlsx') }}"
                    class="cu-btn-secondary whitespace-nowrap">{{ __('common.export') }} {{ __('common.export_xlsx') }}</button>
            </div>
        </form>
    </div>

    <div id="daily-results" class="cu-card cu-card-body">
        <h3 class="cu-section-title mb-4">{{ __('admin.daily_report') }} — {{\App\Support\MyanmarDateFormatter::format(now(), 'Y-m-d')}}</h3>
        <div class="overflow-x-auto">
            <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.staff_id') }}</th>
                        <th>{{ __('common.department') }}</th>
                        <th>{{ __('common.leave_type') }}</th>
                        <th>{{ __('common.total_days') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('common.duty_exchange') }}</th>
                    </tr>
                </thead>
                <tbody id="daily-table-body">
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetch(`{{ route('admin.reports.daily-data') }}?start_date={{ now()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
            .then(res => res.json())
            .then(data => {
                renderDailyResults(data);
            })
            .catch(err => {
                console.error(err);
            });
    });

    function renderDailyResults(data) {
        const resultsDiv = document.getElementById('daily-results');
        const tableBody = document.getElementById('daily-table-body');

        if (!data.table || !data.table.length) {
            resultsDiv.innerHTML = '<h3 class="cu-section-title mb-4">{{ __('admin.daily_report') }} — {{\App\Support\MyanmarDateFormatter::format(now(), 'Y-m-d')}}</h3><p class="text-slate-500 text-sm">{{ __('common.no_data') }}</p>';
            return;
        }

        resultsDiv.classList.remove('hidden');
        tableBody.innerHTML = data.table.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td class="primary">${item.staff_name}</td>
                <td>${item.staff_id}</td>
                <td>${item.department}</td>
                <td>${item.leave_type}</td>
                <td>${item.is_not_limited ? '-' : item.total_days}</td>
                <td>${item.status}</td>
                <td>${item.duty_exchange}</td>
            </tr>
        `).join('');
    }
</script>
@endpush
@endsection
