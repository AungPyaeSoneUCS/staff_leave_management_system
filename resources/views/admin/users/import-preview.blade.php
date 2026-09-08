@extends('layouts.app')

@section('title', __('admin.import_users'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.import_users') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.review_conflicts') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <div class="flex justify-between items-center mb-4">
            <h3 class="cu-section-title">{{ __('admin.import_users') }} - {{ __('common.preview') }}</h3>
            <span class="text-sm text-slate-500">{{ __('admin.preview_rows_found', ['count' => count($previewData)]) }}</span>
        </div>

        @if($hasConflicts)
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-sm text-amber-800">
                    <strong>{{ __('admin.conflicts_detected') }}</strong>
                </p>
            </div>
        @endif

        <form action="{{ route('admin.users.import-process') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="cu-table">
                    <thead>
                        <tr>
                            <th width="30">{{ __('common.number') }}</th>
                            <th>{{ __('common.name') }}</th>
                            <th>{{ __('common.email') }}</th>
                            <th>{{ __('common.staff_id') }}</th>
                            <th>{{ __('common.role') }}</th>
                            <th>{{ __('common.department') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th>{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewData as $index => $row)
                            <tr class="{{ $row['conflict_type'] ? 'bg-amber-50' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div>
                                        <span class="font-medium">{{ $row['name'] }}</span>
                                        @if($row['name_mm'])
                                            <span class="text-xs text-slate-500 block">{{ $row['name_mm'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $row['email'] }}</td>
                                <td>{{ $row['staff_id'] ?? '-' }}</td>
                                <td class="capitalize">{{ __('common.role.' . $row['role']) }}</td>
                                <td>{{ $departments[$row['department_id']] ?? '-' }}</td>
                                <td>
                                    @if($row['conflict_type'])
                                        <span class="cu-badge-warning">
                                            {{ $row['conflict_type'] === 'both' ? __('admin.email_staff_id_conflict') : ($row['conflict_type'] === 'email' ? __('admin.email_exists') : __('admin.staff_id_exists')) }}
                                        </span>
                                    @else
                                        <span class="cu-badge-success">{{ __('admin.new_user') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row['conflict_type'])
                                        <div class="flex gap-2">
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="actions[{{ $index }}]" value="skip" checked>
                                                <span class="text-sm">{{ __('admin.skip') }}</span>
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="actions[{{ $index }}]" value="replace">
                                                <span class="text-sm">{{ __('admin.replace') }}</span>
                                            </label>
                                        </div>
                                        @if($row['existing_user'])
                                            <p class="text-xs text-slate-500 mt-1">
                                                {{ __('admin.existing_user', ['name' => $row['existing_user']['name'], 'email' => $row['existing_user']['email']]) }}
                                            </p>
                                        @endif
                                    @else
                                        <input type="hidden" name="actions[{{ $index }}]" value="import">
                                        <span class="text-sm text-slate-500">{{ __('admin.will_be_added') }}</span>
                                    @endif
                                    <input type="hidden" name="rows[]" value="{{ $index }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('admin.confirm_import') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
