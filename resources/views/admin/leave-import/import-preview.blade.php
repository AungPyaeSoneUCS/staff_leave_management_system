@extends('layouts.app')

@section('title', __('admin.import_leave'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.import_leave') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.review_conflicts') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <div class="flex justify-between items-center mb-4">
            <h3 class="cu-section-title">{{ __('admin.import_leave') }} - {{ __('common.preview') }}</h3>
            <span class="text-sm text-slate-500">{{ __('admin.preview_rows_found', ['count' => count($previewData)]) }}</span>
        </div>

        @if(!empty($skippedCount))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-sm text-amber-800">
                    {{ __('admin.leave_import_skipped_hint', ['skipped' => $skippedCount]) }}
                </p>
            </div>
        @endif

        <form action="{{ route('admin.leave-import.process') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="cu-table">
                    <thead>
                        <tr>
                            <th width="30">{{ __('common.number') }}</th>
                            <th>{{ __('common.name') }}</th>
                            <th>{{ __('common.staff_id') }}</th>
                            <th>{{ __('common.leave_type') }}</th>
                            <th>{{ __('common.start_date') }}</th>
                            <th>{{ __('common.end_date') }}</th>
                            <th>{{ __('common.total_days') }}</th>
                            <th>{{ __('common.duty_exchange') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th>{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($previewData as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="font-medium">{{ $row['staff_name'] }}</span>
                                </td>
                                <td>{{ $row['staff_id'] ?? '-' }}</td>
                                <td>{{ $row['leave_type_name'] }}</td>
                                <td>{{ $row['start_date'] }}</td>
                                <td>{{ $row['end_date'] }}</td>
                                <td>{{ my_number($row['total_days']) }}</td>
                                <td>
                                    @if(!empty($row['duty_exchange_user_id']))
                                        <span>{{ $row['duty_exchange_name'] }}</span>
                                        @if(!empty($row['duty_exchange_staff_id']))
                                            <span class="text-xs text-slate-500 block">{{ $row['duty_exchange_staff_id'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="cu-badge-{{ $row['status'] === 'approved' ? 'success' : 'warning' }}">
                                        {{ __('common.' . $row['status']) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <label class="flex items-center gap-1">
                                            <input type="radio" name="actions[{{ $index }}]" value="import" checked>
                                            <span class="text-sm">{{ __('admin.import') }}</span>
                                        </label>
                                        <label class="flex items-center gap-1">
                                            <input type="radio" name="actions[{{ $index }}]" value="skip">
                                            <span class="text-sm">{{ __('admin.skip') }}</span>
                                        </label>
                                    </div>
                                    <input type="hidden" name="rows[]" value="{{ $index }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-slate-500 py-6">{{ __('common.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.leave-import.index') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('admin.confirm_import') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
