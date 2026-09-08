@extends('layouts.app')

@section('title', __('admin.edit_holiday'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('admin.edit_holiday') }}</h2>
        <p class="cu-muted mb-6">{{ __('admin.edit_holiday_subtitle') }}</p>

        <form action="{{ route('admin.holidays.update', $holiday) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="cu-label">{{ __('common.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $holiday->name) }}" class="cu-input" required>
                @error('name')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name_mm" class="cu-label">{{ __('common.name') }} (မြန်မာ)</label>
                <input type="text" name="name_mm" id="name_mm" value="{{ old('name_mm', $holiday->name_mm) }}" class="cu-input">
                @error('name_mm')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="date" class="cu-label">{{ __('common.date') }}</label>
                <input type="date" name="date" id="date" value="{{ old('date', $holiday->date->format('Y-m-d')) }}" class="cu-input" required>
                @error('date')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="cu-label">{{ __('common.description') }}</label>
                <textarea name="description" id="description" rows="3" class="cu-textarea">{{ old('description', $holiday->description) }}</textarea>
                @error('description')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description_mm" class="cu-label">{{ __('common.description') }} (မြန်မာ)</label>
                <textarea name="description_mm" id="description_mm" rows="3" class="cu-textarea">{{ old('description_mm', $holiday->description_mm) }}</textarea>
                @error('description_mm')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_default" value="1"
                           {{ old('is_default', $holiday->is_default) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-cu-600 focus:ring-cu-500">
                    <span class="text-sm text-slate-700">{{ __('admin.default_holiday') }}</span>
                </label>
                <p class="text-xs text-slate-500 mt-1">{{ __('admin.default_holiday_hint') }}</p>
            </div>

            <div class="mb-4">
                <label for="replaced_holiday_id" class="cu-label">{{ __('admin.replaces_holiday') }}</label>
                <select name="replaced_holiday_id" id="replaced_holiday_id" class="cu-select">
                    <option value="">{{ __('common.select_option') }}</option>
                    @foreach(\App\Models\Holiday::where('is_default', true)->get() as $default)
                        <option value="{{ $default->id }}" {{ old('replaced_holiday_id', $holiday->replaced_holiday_id) == $default->id ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'my' ? ($default->name_mm ?? $default->name) : $default->name }}
                        </option>
                    @endforeach
                </select>
                @error('replaced_holiday_id')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="replacement_note" class="cu-label">{{ __('admin.replacement_note') }}</label>
                <textarea name="replacement_note" id="replacement_note" rows="2" class="cu-textarea" placeholder="{{ __('admin.replacement_note_placeholder') }}">{{ old('replacement_note', $holiday->replacement_note) }}</textarea>
                @error('replacement_note')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.holidays.index') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection