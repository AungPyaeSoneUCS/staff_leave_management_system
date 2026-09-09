@extends('layouts.app')

@section('title', __('admin.import_leave'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.import_leave') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.import_leave_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form action="{{ route('admin.leave-import.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="import_file" class="cu-label">{{ __('common.file') }}</label>
                    <input type="file" name="import_file" id="import_file" accept=".xlsx,.csv" class="cu-input" required>
                    <p class="text-xs text-slate-500 mt-1">{{ __('common.xlsx_or_csv_format') }}</p>
                    @error('import_file')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.leave-import.template') }}" class="cu-btn-secondary">{{ __('admin.download_template') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('common.next') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
