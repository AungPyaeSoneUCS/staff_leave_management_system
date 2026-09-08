@extends('layouts.app')

@section('title', __('admin.departments_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.departments_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.departments_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.departments.create') }}" class="cu-btn-primary">{{ __('admin.add_department') }}</a>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.code') }}</th>
                        <th>{{ __('common.description') }}</th>
                        <th>{{ __('common.head') }}</th>
                        <th>{{ __('common.staff_count') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($departments as $department)
                    <tr>
                        <td>{{ config('app.locale') == 'my' ? my_number($departments->firstItem() + $loop->index) : $departments->firstItem() + $loop->index }}</td>
                        <td class="primary">{{ app()->getLocale() == 'my' ? $department->name_mm ?? $department->name : $department->name }}</td>
                        <td>{{ $department->code }}</td>
                        <td>{{ $department->description ?: '—' }}</td>
                        <td>{{ $department->head ? app()->getLocale() == 'my' ? $department->head->name_mm ?? $department->head->name : $department->head->name : __('common.not_assigned') }}</td>
                        <td>{{ my_number($department->users_count) }}</td>
                        <td class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.departments.edit', $department) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.edit') }}</a>
                            <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cu-btn-danger !px-3 !py-1.5 !rounded-full text-xs"
                                        data-confirm="{{ __('admin.delete_this_department') }}">{{ __('common.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $departments->links() }}
    </div>
</div>
@endsection
