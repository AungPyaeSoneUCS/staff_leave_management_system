@extends('layouts.app')

@section('title', __('admin.dashboard_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.dashboard_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.dashboard_subtitle') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-dashboard-widget
            title="{{ __('admin.total_staff') }}"
            :value="my_number($statistics['total_staff'])"
            color="blue"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'/>" />

        <x-dashboard-widget
            title="{{ __('admin.active_departments') }}"
            :value="my_number($statistics['total_departments'])"
            color="green"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'/>" />

        <x-dashboard-widget
            title="{{ __('admin.pending_requests') }}"
            :value="my_number($statistics['pending_requests'])"
            color="yellow"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/>" />

        <x-dashboard-widget
            title="{{ __('admin.approved_today') }}"
            :value="my_number($statistics['approved_today'])"
            color="green"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/>" />

        <x-dashboard-widget
            title="{{ __('admin.rejected_today') }}"
            :value="my_number($statistics['rejected_today'])"
            color="red"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'/>" />

        <x-dashboard-widget
            title="{{ __('admin.approved_this_month') }}"
            :value="my_number($statistics['approved_this_month'])"
            color="blue"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'/>" />

        <x-dashboard-widget
            title="{{ __('admin.rejected_this_month') }}"
            :value="my_number($statistics['rejected_this_month'])"
            color="red"
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'/>" />
    </div>
</div>
@endsection
