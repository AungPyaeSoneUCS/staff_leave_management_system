@extends('layouts.app')

@section('title', __('admin.edit_leave_type'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('admin.edit_leave_type') }}</h2>
        <p class="cu-muted mb-6">{{ app()->getLocale() == 'my' ? $leaveType->name_mm ?? $leaveType->name : $leaveType->name }}</p>

        <form action="{{ route('admin.leave-types.update', $leaveType) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="cu-label">{{ __('common.leave_type_name') ?? __('common.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $leaveType->name) }}" class="cu-input">
                @error('name')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name_mm" class="cu-label">{{ __('common.leave_type_name_mm') }}</label>
                <input type="text" name="name_mm" id="name_mm" value="{{ old('name_mm', $leaveType->name_mm) }}" class="cu-input">
                @error('name_mm')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="code" class="cu-label">{{ __('common.code') }}</label>
                <input type="text" name="code" id="code" value="{{ old('code', $leaveType->code) }}" class="cu-input">
                @error('code')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="cu-label">{{ __('common.description') }}</label>
                <textarea name="description" id="description" rows="3" class="cu-textarea">{{ old('description', $leaveType->description) }}</textarea>
                @error('description')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description_mm" class="cu-label">{{ __('common.description_mm') }}</label>
                <textarea name="description_mm" id="description_mm" rows="3" class="cu-textarea">{{ old('description_mm', $leaveType->description_mm) }}</textarea>
                @error('description_mm')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_not_limited" value="1"
                           {{ old('is_not_limited', $leaveType->is_not_limited) ? 'checked' : '' }}
                           class="cu-checkbox" id="is_not_limited">
                    <span class="text-sm text-slate-700">{{ __('admin.is_not_limited') }}</span>
                </label>
            </div>

            <div class="mb-4" id="annual-allocation-group">
                <label for="annual_allocation" class="cu-label">{{ __('common.annual_allocation') }}</label>
                <input type="number" name="annual_allocation" id="annual_allocation"
                       value="{{ old('annual_allocation', $leaveType->annual_allocation) }}"
                       min="1" class="cu-input">
                @error('annual_allocation')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="per_leave_days" class="cu-label">{{ __('common.per_leave_days') }}</label>
                <input type="number" name="per_leave_days" id="per_leave_days"
                       value="{{ old('per_leave_days', $leaveType->per_leave_days) }}"
                       min="1" class="cu-input" placeholder="{{ __('common.optional') }}">
                <p class="mt-1 text-sm text-slate-500">{{ __('common.per_leave_days_hint') }}</p>
                @error('per_leave_days')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="track_on_use" value="1"
                           {{ old('track_on_use', $leaveType->track_on_use) ? 'checked' : '' }}
                           class="cu-checkbox" id="track_on_use">
                    <span class="text-sm text-slate-700">{{ __('admin.track_on_use') }}</span>
                </label>
                <p class="mt-1 ml-6 text-xs text-slate-500">{{ __('admin.track_on_use_hint') }}</p>
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="requires_attachment" value="1"
                           {{ old('requires_attachment', $leaveType->requires_attachment) ? 'checked' : '' }}
                           class="cu-checkbox">
                    <span class="text-sm text-slate-700">{{ __('common.requires_attachment') }}</span>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.leave-types.index') }}" class="cu-btn-amber-nude">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('admin.edit_leave_type') }}</button>
            </div>
        </form>

        <form action="{{ route('admin.leave-types.reallocate', $leaveType) }}" method="POST" class="mt-8 pt-6 border-t border-slate-200">
            @csrf
            @method('POST')
            <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('admin.reallocate_balances') }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ __('admin.reallocate_balances_hint') }}</p>
            <div class="flex items-center gap-4">
                <input type="number" name="new_allocation" value="{{ old('new_allocation', $leaveType->annual_allocation) }}" min="1" class="cu-input w-32">
                <button type="submit" class="cu-btn-primary" data-confirm="{{ __('admin.reallocate_confirm') }}">{{ __('admin.reallocate') }}</button>
            </div>
            @error('new_allocation')
                <p class="cu-form-error mt-2">{{ $message }}</p>
            @enderror
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('is_not_limited').addEventListener('change', function() {
    const allocationGroup = document.getElementById('annual-allocation-group');
    const allocationInput = document.getElementById('annual_allocation');
    if (this.checked) {
        allocationGroup.classList.add('hidden');
        allocationInput.value = '';
    } else {
        allocationGroup.classList.remove('hidden');
    }
});

if (document.getElementById('is_not_limited').checked) {
    document.getElementById('annual-allocation-group').classList.add('hidden');
    document.getElementById('annual_allocation').value = '';
}
</script>
@endpush
@endsection
