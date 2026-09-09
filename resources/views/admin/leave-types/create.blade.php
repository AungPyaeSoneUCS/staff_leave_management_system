@extends('layouts.app')

@section('title', __('admin.create_leave_type'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('admin.create_leave_type') }}</h2>
        <p class="cu-muted mb-6">{{ __('admin.define_category') }}</p>

        <form action="{{ route('admin.leave-types.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="name" class="cu-label">{{ __('common.leave_type_name') ?? __('common.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="cu-input">
                @error('name')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name_mm" class="cu-label">{{ __('common.leave_type_name_mm') }}</label>
                <input type="text" name="name_mm" id="name_mm" value="{{ old('name_mm') }}" class="cu-input">
                @error('name_mm')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="code" class="cu-label">{{ __('common.code') }}</label>
                <input type="text" name="code" id="code" value="{{ old('code') }}" class="cu-input">
                @error('code')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="cu-label">{{ __('common.description') }}</label>
                <textarea name="description" id="description" rows="3" class="cu-textarea">{{ old('description') }}</textarea>
                @error('description')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description_mm" class="cu-label">{{ __('common.description_mm') }}</label>
                <textarea name="description_mm" id="description_mm" rows="3" class="cu-textarea">{{ old('description_mm') }}</textarea>
                @error('description_mm')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_not_limited" value="1"
                           {{ old('is_not_limited') ? 'checked' : '' }}
                           class="cu-checkbox" id="is_not_limited">
                    <span class="text-sm text-slate-700">{{ __('admin.is_not_limited') }}</span>
                </label>
            </div>

            <div class="mb-4" id="annual-allocation-group">
                <label for="annual_allocation" class="cu-label">{{ __('common.annual_allocation') }}</label>
                <input type="number" name="annual_allocation" id="annual_allocation" value="{{ old('annual_allocation') }}"
                       min="1" class="cu-input">
                @error('annual_allocation')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="per_leave_days" class="cu-label">{{ __('common.per_leave_days') }}</label>
                <input type="number" name="per_leave_days" id="per_leave_days" value="{{ old('per_leave_days') }}"
                       min="1" class="cu-input" placeholder="{{ __('common.optional') }}">
                <p class="mt-1 text-sm text-slate-500">{{ __('common.per_leave_days_hint') }}</p>
                @error('per_leave_days')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="track_on_use" value="1"
                           {{ old('track_on_use') ? 'checked' : '' }}
                           class="cu-checkbox" id="track_on_use">
                    <span class="text-sm text-slate-700">{{ __('admin.track_on_use') }}</span>
                </label>
                <p class="mt-1 ml-6 text-xs text-slate-500">{{ __('admin.track_on_use_hint') }}</p>
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="requires_attachment" value="1"
                           {{ old('requires_attachment') ? 'checked' : '' }}
                           class="cu-checkbox">
                    <span class="text-sm text-slate-700">{{ __('common.requires_attachment') }}</span>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.leave-types.index') }}" class="cu-btn-amber-nude">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('admin.create_leave_type') }}</button>
            </div>
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
</script>
@endpush
@endsection
