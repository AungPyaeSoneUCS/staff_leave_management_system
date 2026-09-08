@extends('layouts.app')

@section('title', __('admin.edit_department'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('admin.edit_department') }}</h2>
        <p class="cu-muted mb-6">{{ app()->getLocale() == 'my' ? $department->name_mm ?? $department->name : $department->name }}</p>

        <form action="{{ route('admin.departments.update', $department) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="cu-label">{{ __('common.department_name') ?? __('common.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $department->name) }}" class="cu-input">
                @error('name')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name_mm" class="cu-label">{{ __('common.department_name_mm') }}</label>
                <input type="text" name="name_mm" id="name_mm" value="{{ old('name_mm', $department->name_mm) }}" class="cu-input">
                @error('name_mm')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="code" class="cu-label">{{ __('common.code') }}</label>
                <input type="text" name="code" id="code" value="{{ old('code', $department->code) }}" class="cu-input">
                @error('code')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="cu-label">{{ __('common.description') }}</label>
                <textarea name="description" id="description" rows="3" class="cu-textarea">{{ old('description', $department->description) }}</textarea>
                @error('description')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="head_id" class="cu-label">{{ __('common.department_head') }}</label>
                <select name="head_id" id="head_id" class="cu-select">
                    <option value="">{{ __('common.no_head_assigned') }}</option>
                    @foreach($potentialHeads as $head)
                            <option value="{{ $head->id }}" {{ old('head_id', $department->head_id) == $head->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'my' ? $head->name_mm ?? $head->name : $head->name }}
                            </option>
                    @endforeach
                </select>
                @error('head_id')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.departments.index') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('admin.edit_department') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
