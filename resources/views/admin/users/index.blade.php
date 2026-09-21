@extends('layouts.app')

@section('title', __('admin.users_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.users_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.users_subtitle') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.create') }}" class="cu-btn-primary">{{ __('admin.add_user') }}</a>
            <button type="button" onclick="document.getElementById('import-modal').classList.remove('hidden')" class="cu-btn-secondary">{{ __('admin.import_users') }}</button>
        </div>
    </div>

    <div id="import-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('admin.import_users') }}</h3>
                    <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="import_file" class="cu-label">{{ __('common.file') }}</label>
                        <input type="file" name="import_file" id="import_file" accept=".xlsx,.csv" class="cu-input" required>
                        <p class="text-xs text-slate-500 mt-1">{{ __('common.xlsx_or_csv_format') }}</p>
                        @error('import_file')
                            <p class="cu-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.users.import-template') }}" class="cu-btn-secondary text-sm">{{ __('admin.download_template') }}</a>
                        <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')" class="cu-btn-amber-nude">{{ __('common.cancel') }}</button>
                        <button type="submit" class="cu-btn-primary">{{ __('common.next') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" id="user-filter-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="user-search" class="cu-label">{{ __('common.search_with_name') }}</label>
                <input type="text" name="search" id="user-search" value="{{ request('search') }}" placeholder="{{ __('common.search') }}..." class="cu-input" autocomplete="off">
            </div>
            <div>
                <label for="user-filter-role" class="cu-label">{{ __('common.role') }}</label>
                <select name="role" id="user-filter-role" class="cu-select">
                    <option value="">{{ __('common.all_roles') }}</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>{{ __('common.role.admin') }}</option>
                    <option value="department_head" {{ request('role') == 'department_head' ? 'selected' : '' }}>{{ __('common.role.department_head') }}</option>
                    <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>{{ __('common.role.staff') }}</option>
                </select>
            </div>
            <div>
                <label for="user-filter-department" class="cu-label">{{ __('common.department') }}</label>
                <select name="department_id" id="user-filter-department" class="cu-select">
                    <option value="">{{ __('common.all_departments') }}</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'my' ? ($department->name_mm ?? $department->name) : $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="user-filter-position" class="cu-label">{{ __('common.position') }}</label>
                <select name="position" id="user-filter-position" class="cu-select">
                    <option value="">{{ __('common.all_positions') }}</option>
                    @foreach($positions as $en => $mm)
                        <option value="{{ $en }}" {{ request('position') == $en ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'my' ? ($mm ?: $en) : $en }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @include('admin.users.results', ['users' => $users, 'sort' => $sort, 'direction' => $direction])
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const userFilterForm = document.getElementById('user-filter-form');
            const userSearchInput = document.getElementById('user-search');
            const userResults = document.getElementById('user-results');
            const userFormAction = userFilterForm ? userFilterForm.getAttribute('action') : '';
            let userFilterTimer;
            let userFilterController = null;

            function userFetchResults() {
                if (!userFilterForm || !userResults || !userFormAction) return;

                const params = new URLSearchParams(new FormData(userFilterForm));

                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('sort')) params.set('sort', urlParams.get('sort'));
                if (urlParams.get('direction')) params.set('direction', urlParams.get('direction'));

                if (userFilterController) userFilterController.abort();
                userFilterController = new AbortController();

                fetch(userFormAction + '?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: userFilterController.signal,
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Request failed');
                        return response.text();
                    })
                    .then(function (html) {
                        userResults.innerHTML = html;
                    })
                    .catch(function (err) {
                        if (err.name === 'AbortError') return;
                    });
            }

            if (userSearchInput) {
                userSearchInput.addEventListener('input', function () {
                    clearTimeout(userFilterTimer);
                    userFilterTimer = setTimeout(userFetchResults, 400);
                });
            }

            if (userFilterForm) {
                userFilterForm.querySelectorAll('select').forEach(function (select) {
                    select.addEventListener('change', userFetchResults);
                });
            }
        })();
    </script>
@endpush
