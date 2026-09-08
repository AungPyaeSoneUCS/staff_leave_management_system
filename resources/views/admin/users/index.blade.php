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
                        <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')" class="cu-btn-secondary">{{ __('common.cancel') }}</button>
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
                <input type="text" name="search" id="user-search" list="user-name-suggestions" value="{{ request('search') }}" placeholder="{{ __('common.search') }}..." class="cu-input" autocomplete="off">
                <datalist id="user-name-suggestions">
                    @foreach($nameSuggestions as $suggestion)
                        <option value="{{ $suggestion }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label for="user-filter-role" class="cu-label">{{ __('common.role') }}</label>
                <select name="role" id="user-filter-role" class="cu-select">
                    <option value="">{{ __('common.all_roles') }}</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>{{ __('common.role.admin') }}</option>
                    <option value="department_head" {{ request('role') == 'department_head' ? 'selected' : '' }}>{{ __('common.role.department_head') }}</option></option>
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

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.name') }}
                            </a>
                        </th>
                        <th>{{ __('common.email') }}</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'role', 'direction' => $sort === 'role' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.role') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'department', 'direction' => $sort === 'department' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.department') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'position', 'direction' => $sort === 'position' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.position') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'staff_id', 'direction' => $sort === 'staff_id' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.staff_id') }}
                            </a>
                        </th>
                        <th>{{ __('common.phone') }}</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'is_active', 'direction' => $sort === 'is_active' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.status') }}
                            </a>
                        </th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ config('app.locale') == 'my' ? my_number($users->firstItem() + $loop->index) : $users->firstItem() + $loop->index }}</td>
                        <td class="primary">
                            <div class="flex items-center gap-2">
                                @if($user->profile_image)
                                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                {{ app()->getLocale() == 'my' ? $user->name_mm ?? $user->name : $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span @class([
                                'cu-badge-admin' => $user->role === 'admin',
                                'cu-badge-info' => $user->role === 'department_head',
                                'cu-badge-neutral' => $user->role === 'staff',
                            ])>
                                {{ __('common.role.' . $user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->department ? (app()->getLocale() == 'my' ? ($user->department->name_mm ?? $user->department->name) : $user->department->name) : __('common.n_a') }}</td>
                        <td>{{ app()->getLocale() == 'my' ? $user->position_mm ?? $user->position : $user->position ?? $user->position_mm ?? __('common.n_a') }}</td>
                        <td>{{ $user->staff_id ? $user->staff_id : __('common.n_a') }}</td>
                        <td>{{ my_phone($user->phone) }}</td>
                        <td>
                            <span @class([
                                'cu-badge-success' => $user->is_active,
                                'cu-badge-danger' => ! $user->is_active,
                            ])>
                                {{ $user->is_active ? __('common.staff.active') : __('common.staff.inactive') }}
                            </span>
                        </td>
                        <td class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.edit') }}</a>
                            <a href="{{ route('admin.staff.show', $user) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.view') }}</a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cu-btn-danger !px-3 !py-1.5 !rounded-full text-xs"
                                            data-confirm="{{ __('admin.delete_this_user') }}">{{ __('common.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const userFilterForm = document.getElementById('user-filter-form');
        const userSearchInput = document.getElementById('user-search');
        let userFilterTimer;

        if (userSearchInput) {
            userSearchInput.addEventListener('input', function () {
                clearTimeout(userFilterTimer);
                userFilterTimer = setTimeout(() => userFilterForm.submit(), 500);
            });
        }

        if (userFilterForm) {
            userFilterForm.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', () => userFilterForm.submit());
            });
        }
    </script>
@endpush
