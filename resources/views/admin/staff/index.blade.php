@extends('layouts.app')

@section('title', __('admin.staff_information'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.staff_information') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.staff_information_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
            <thead>
                <tr>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('common.staff_id') }}</th>
                    <th>{{ __('common.email') }}</th>
                    <th>{{ __('common.department') }}</th>
                    <th>{{ __('common.position') }}</th>
                    <th>{{ __('common.phone') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $member)
                    <tr>
                        <td class="primary">
                            {{ app()->getLocale() == 'my' ? $member->name_mm ?? $member->name : $member->name }}
                        </td>
                        <td>{{ $member->staff_id ? $member->staff_id : __('common.n_a') }}</td>
                        <td>{{ $member->email }}</td>
                        <td>{{ $member->department?->name ?? __('common.n_a') }}</td>
                        <td>
                                {{ app()->getLocale() == 'my' ? $member->position_mm ?? $member->position : $member->position ?? $member->position_mm ?? __('common.n_a') }}
                        </td>
                        <td>{{ $member->phone ? my_number($member->phone) : __('common.n_a') }}</td>
                        <td>
                            <span @class([
                                'cu-badge-success' => $member->is_active,
                                'cu-badge-danger' => ! $member->is_active,
                            ])>
                                {{ $member->is_active ? __('common.staff.active') : __('common.staff.inactive') }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.staff.show', $member) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 py-8">{{ __('common.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
