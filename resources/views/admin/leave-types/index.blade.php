@extends('layouts.app')

@section('title', __('admin.leave_types_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.leave_types_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.leave_types_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.leave-types.create') }}" class="cu-btn-primary">{{ __('admin.add_leave_type') }}</a>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.code') }}</th>
                        <th>{{ __('common.description') }}</th>
                        <th>{{ __('common.annual_allocation') }}</th>
                        <th>{{ __('common.per_leave_days') }}</th>
                        <th>{{ __('common.requires_attachment') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($leaveTypes as $leaveType)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="primary">{{ app()->getLocale() == 'my' ? $leaveType->name_mm ?? $leaveType->name : $leaveType->name }}</td>
                        <td>{{ $leaveType->code }}</td>
                        <td class="line-clamp-2 text-sm">{{ app()->getLocale() == 'my' ? ($leaveType->description_mm ?? $leaveType->description ?: '—') : ($leaveType->description ?: '—') }}</td>
                        <td>{{ $leaveType->annual_allocation }}</td>
                        <td>{{ $leaveType->per_leave_days ?? '—' }}</td>
                        <td>{{ $leaveType->requires_attachment ? __('common.required') : __('common.optional') }}</td>
                        <td>
                            <span @class([
                                'cu-badge-success' => $leaveType->is_active,
                                'cu-badge-danger' => ! $leaveType->is_active,
                            ])>
                                {{ $leaveType->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.leave-types.edit', $leaveType) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.edit') }}</a>
                            <form action="{{ route('admin.leave-types.destroy', $leaveType) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cu-btn-danger !px-3 !py-1.5 !rounded-full text-xs"
                                        data-confirm="{{ __('admin.delete_this_leave_type') }}">{{ __('common.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $leaveTypes->links() }}
    </div>
</div>
@endsection
