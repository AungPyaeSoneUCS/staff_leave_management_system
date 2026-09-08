@extends('layouts.app')

@section('title', __('admin.add_holiday'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('admin.add_holiday') }}</h2>
        <p class="cu-muted mb-6">{{ __('admin.add_holiday_subtitle') }}</p>

        <form action="{{ route('admin.holidays.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="cu-label">{{ __('common.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="cu-input" required>
                @error('name')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name_mm" class="cu-label">{{ __('common.name') }} (မြန်မာ)</label>
                <input type="text" name="name_mm" id="name_mm" value="{{ old('name_mm') }}" class="cu-input">
                @error('name_mm')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="date" class="cu-label">{{ __('common.date') }}</label>
                <input type="date" name="date" id="date" value="{{ old('date') }}" class="cu-input" required>
                @error('date')
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
                <label for="description_mm" class="cu-label">{{ __('common.description') }} (မြန်မာ)</label>
                <textarea name="description_mm" id="description_mm" rows="3" class="cu-textarea">{{ old('description_mm') }}</textarea>
                @error('description_mm')
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