@extends('layouts.app')

@section('title', __('super_admin.assignments_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('super_admin.assignments_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('super_admin.assignments_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form action="{{ route('super-admin.assignments.store') }}" method="POST" class="mb-6">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="admin_id" class="cu-label">{{ __('super_admin.assign_to_admin') }}</label>
                    <select name="admin_id" id="admin_id" class="cu-select" required>
                        <option value="">{{ __('common.select_admin') }}</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ old('admin_id') == $admin->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'my' ? $admin->name_mm ?? $admin->name : $admin->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('admin_id')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="reason" class="cu-label">{{ __('common.reason') }}</label>
                    <textarea name="reason" id="reason" rows="3" class="cu-textarea">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="cu-btn-primary">{{ __('super_admin.assign') }}</button>
            </div>
        </form>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
            <thead>
                <tr>
                    <th>{{ __('common.number') }}</th>
                    <th>{{ __('super_admin.admin_name') }}</th>
                    <th>{{ __('common.reason') }}</th>
                    <th>{{ __('super_admin.assigned_at') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="primary">
                            {{ app()->getLocale() == 'my' ? ($assignment->admin->name_mm ?? $assignment->admin->name) : $assignment->admin->name }}
                        </td>
                        <td class="line-clamp-2 text-sm">{{ $assignment->reason ?: '—' }}</td>
                        <td>{{\App\Support\MyanmarDateFormatter::format($assignment->created_at, 'M d, Y H:i')}}</td>
                        <td>
                            <form action="{{ route('super-admin.assignments.destroy', $assignment) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cu-btn-danger !px-3 !py-1.5 !rounded-full text-xs"
                                        data-confirm="{{ __('super_admin.remove_assignment') }}">{{ __('common.remove') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-slate-500">{{ __('super_admin.no_assignments') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $assignments->links() }}
    </div>
</div>
@endsection
