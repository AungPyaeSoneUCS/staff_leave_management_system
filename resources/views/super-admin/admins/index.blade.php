@extends('layouts.app')

@section('title', __('super_admin.admins_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('super_admin.admins_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('super_admin.admins_subtitle') }}</p>
        </div>
        <a href="{{ route('super-admin.admins.create') }}" class="cu-btn-primary">{{ __('super_admin.create_admin') }}</a>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
            <thead>
                <tr>
                    <th>{{ __('common.number') }}</th>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('common.email') }}</th>
                    <th>{{ __('common.department') }}</th>
                    <th>{{ __('common.position') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="primary">
                            <div class="flex items-center gap-2">
                                @if($admin->profile_image)
                                    <img src="{{ asset('storage/' . $admin->profile_image) }}" alt="{{ $admin->name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                {{ app()->getLocale() == 'my' ? $admin->name_mm ?? $admin->name : $admin->name }}
                            </div>
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->department ? $admin->department->name : __('common.n_a') }}</td>
                        <td>{{ app()->getLocale() == 'my' ? $admin->position_mm ?? $admin->position : $admin->position ?? $admin->position_mm ?? __('common.n_a') }}</td>
                        <td>
                            <span @class([
                                'cu-badge-success' => $admin->is_active,
                                'cu-badge-danger' => ! $admin->is_active,
                            ])>
                                {{ $admin->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="flex items-center justify-center gap-2">
                            <a href="{{ route('super-admin.admins.edit', $admin) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.edit') }}</a>
                            @if($admin->id !== auth()->id())
                                <form action="{{ route('super-admin.admins.toggle-active', $admin) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">
                                        {{ $admin->is_active ? __('common.deactivate') : __('common.activate') }}
                                    </button>
                                </form>
                                <form action="{{ route('super-admin.admins.destroy', $admin) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cu-btn-danger !px-3 !py-1.5 !rounded-full text-xs"
                                            data-confirm="{{ __('super_admin.delete_this_admin') }}">{{ __('common.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-500">{{ __('super_admin.no_admins') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $admins->links() }}
    </div>
</div>
@endsection
