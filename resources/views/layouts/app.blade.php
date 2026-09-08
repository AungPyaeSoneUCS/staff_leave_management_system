<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/ucsh_logo.jpg') }}">
    <title>@yield('title', __('app.name')) — {{ __('app.university') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg font-sans antialiased text-slate-800">
    <div class="flex min-h-screen">
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()">
        </div>

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-primary-500 text-white transform -translate-x-full lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen transition-transform duration-200 ease-in-out flex flex-col shrink-0">
            <div class="flex items-center gap-3 px-6 h-24 border-b border-white/10 shrink-0">
                <div class="w-16 overflow-hidden shadow-md shadow-primary-500/20">
                    <img src="{{ asset('images/ucsh_logo.jpg') }}" class="w-full h-full" />
                </div>
                <div class="flex flex-col leading-tight min-w-0">
                    <span class="text-white font-bold tracking-tight py-1">{{ __('app.name') }}</span>
                    <span
                        class="text-white/80 text-[10px] font-medium uppercase tracking-wider py-1">{{ __('app.university') }}</span>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto custom-scrollbar">
                @auth
                    @if(auth()->user()->isStaff())
                        <a href="{{ route('staff.dashboard') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('staff.dashboard') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                            <span>{{ __('nav.dashboard') }}</span>
                        </a>
                        <a href="{{ route('staff.leave-requests.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('staff.leave-requests.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                </path>
                            </svg>
                            <span>{{ __('nav.leave_requests') }}</span>
                        </a>
                        {{-- <a href="{{ route('staff.calendar') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('staff.calendar') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>{{ __('nav.calendar') }}</span>
                        </a> --}}
                        <a href="{{ route('staff.profile.edit') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('staff.profile.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ __('nav.profile') }}</span>
                        </a>
                        <div class="relative">
                            <button type="button" onclick="toggleLeaveTypesNavDropdown()"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('staff.leave-types.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span>{{ __('nav.leave_types') }}</span>
                                </div>
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        <div id="leave-types-nav-dropdown"
                            class="mt-1 ml-4 space-y-1 {{ request()->routeIs('staff.leave-types.*') ? '' : 'hidden' }}">
                            @php
                                $staffLeaveTypes = \App\Models\LeaveType::where('is_active', true)->get();
                            @endphp
                            @foreach($staffLeaveTypes as $lt)
                                <a href="{{ route('staff.leave-types.show', $lt) }}"
                                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition-colors">
                                    <div class="font-semibold">
                                        {{ app()->getLocale() == 'my' ? ($lt->name_mm ?? $lt->name) : $lt->name }}</div>
                                    {{-- @if($lt->description)
                                    <div class="text-xs text-white/50 mt-0.5 line-clamp-2">{{ app()->getLocale() == 'my' ?
                                        ($lt->description_mm ?? $lt->description) : $lt->description }}</div>
                                    @endif --}}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if(auth()->user()->isDepartmentHead())
                        <a href="{{ route('department-head.dashboard') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('department-head.dashboard') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                            <span>{{ __('nav.dashboard') }}</span>
                        </a>
                        <a href="{{ route('department-head.leave-requests.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('department-head.leave-requests.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                </path>
                            </svg>
                            <span>{{ __('nav.leave_requests') }}</span>
                        </a>
                        <a href="{{ route('department-head.approvals.pending') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('department-head.approvals.pending') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ __('nav.pending_approvals') }}</span>
                        </a>
                        <a href="{{ route('department-head.approvals.history') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('department-head.approvals.history') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ __('nav.history') }}</span>
                        </a>
                        <a href="{{ route('department-head.reports.leave') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('department-head.reports.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            <span>{{ __('department_head.leave_report') }}</span>
                        </a>
                        <a href="{{ route('department-head.staff.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('department-head.staff.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            <span>{{ __('nav.staff_information') }}</span>
                        </a>
                        <a href="{{ route('department-head.profile.edit') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('department-head.profile.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ __('nav.profile') }}</span>
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                            <span>{{ __('nav.dashboard') }}</span>
                        </a>
                        <a href="{{ route('central-admin.approvals.pending') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('central-admin.approvals.pending') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                </path>
                            </svg>
                            <span>{{ __('nav.pending_approvals') }}</span>
                        </a>
                        <a href="{{ route('central-admin.approvals.history') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('central-admin.approvals.history') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ __('nav.history') }}</span>
                        </a>
                        <a href="{{ route('admin.departments.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.departments.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            <span>{{ __('nav.departments') }}</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.staff.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            <span>{{ __('nav.users') }}</span>
                        </a>
                        {{-- <a href="{{ route('admin.staff.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.staff.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            <span>{{ __('nav.staff_information') }}</span>
                        </a> --}}
                        <a href="{{ route('admin.leave-types.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.leave-types.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>{{ __('nav.leave_types') }}</span>
                        </a>
                        <a href="{{ route('admin.leave-import.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.leave-import.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5">
                                </path>
                            </svg>
                            <span>{{ __('nav.leave_import') }}</span>
                        </a>
                        <a href="{{ route('admin.profile.edit') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.profile.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ __('nav.profile') }}</span>
                        </a>
                        {{-- <a href="{{ route('admin.holidays.calendar') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.holidays.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 15.546c-.523 0-1.046.15-1.546.447a2.498 2.498 0 01-1.7 1.7c-.297.5-.447 1.023-.447 1.546 0 .523.15 1.046.447 1.546a2.498 2.498 0 01-1.7 1.7 2.498 2.498 0 01-1.546-.447 2.498 2.498 0 01-1.546.447c-.523 0-1.046-.15-1.546-.447a2.498 2.498 0 01-1.7-1.7c-.297-.5-.447-1.023-.447-1.546zM12 12.75a.75.75 0 100-1.5.75.75 0 000 1.5zM7.5 7.5a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm9 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z">
                                </path>
                            </svg>
                            <span>{{ __('nav.holidays') }}</span>
                        </a> --}}
                        <div class="relative">
                            <button type="button" onclick="toggleReportsNavDropdown()"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                    <span>{{ __('nav.reports') }}</span>
                                </div>
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </button>
                            <div id="reports-nav-dropdown"
                                class="mt-1 ml-4 space-y-1 {{ request()->routeIs('admin.reports.*') ? '' : 'hidden' }}">
                                <a href="{{ route('admin.reports.leave-summary') }}"
                                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition-colors">{{ __('admin.leave_summary_report') }}</a>
                                <a href="{{ route('admin.reports.balance') }}"
                                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition-colors">{{ __('admin.balance_report') }}</a>
                                <a href="{{ route('admin.reports.leave-type') }}"
                                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition-colors">{{ __('admin.leave_type_report') }}</a>
                                <a href="{{ route('admin.reports.department') }}"
                                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition-colors">{{ __('admin.department_report') }}</a>
                                {{-- <a href="{{ route('admin.reports.daily') }}"
                                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition-colors">{{
                                    __('admin.daily_report') }}</a> --}}
                            </div>
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                            <div class="pt-2 mt-2 border-t border-white/10">
                                <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-white/50">
                                    {{ __('super_admin.section_title') }}</p>
                                <a href="{{ route('super-admin.admins.index') }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('super-admin.admins.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                        </path>
                                    </svg>
                                    <span>{{ __('super_admin.admin_management') }}</span>
                                </a>
                                <a href="{{ route('super-admin.assignments.index') }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('super-admin.assignments.*') ? 'bg-gold-100 text-slate-700' : 'text-white/70 hover:bg-white/5 hover:text-white' }} transition-colors">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                        </path>
                                    </svg>
                                    <span>{{ __('super_admin.assignments') }}</span>
                                </a>
                            </div>
                        @endif
                    @endif
                @endauth
            </nav>

            @auth
                <div class="p-4 border-t border-white/10 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white">
                                {{ app()->getLocale() == 'my' ? auth()->user()->name_mm ?? auth()->user()->name : auth()->user()->name }}
                                <small>@if (auth()->user()->position)
                                    -
                                    {{ app()->getLocale() == 'my' ? auth()->user()->position_mm ?? auth()->user()->position : auth()->user()->position }}
                                @endif</small>
                            </p>
                            @if (auth()->user()->department !== null)
                                <p>
                                    {{ app()->getLocale() == 'my' ? auth()->user()->department->name_mm ?? auth()->user()->department->name : auth()->user()->department->name }}
                                </p>
                            @endif
                            <p class="text-sm font-medium text-white">
                                {{ __('common.role.' . auth()->user()->role) }}
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            <span>{{ __('nav.logout') }}</span>
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="sticky top-0 z-30 bg-primary-200 backdrop-blur-md border-b border-slate-400/80 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()"
                        class="lg:hidden p-2 rounded-xl text-slate-700 hover:bg-slate-100 transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="hidden sm:flex items-center gap-2">
                        <img src="{{ asset('images/ucsh_logo.jpg') }}" class="h-9 w-9 object-contain" />
                        <span class="font-semibold text-slate-900">{{ __('app.name') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center rounded-xl bg-slate-100 p-1">
                        <a href="{{ route('lang.switch', 'en') }}"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ session('locale', 'en') === 'en' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-700 hover:text-slate-900' }}">{{ __('nav.english') }}</a>
                        <a href="{{ route('lang.switch', 'my') }}"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ session('locale') === 'my' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-700 hover:text-slate-900' }}">{{ __('nav.myanmar') }}</a>
                    </div>
                    @auth
                        <div class="relative" id="notification-wrapper">
                            <button
                                class="relative p-2 rounded-xl text-slate-700 hover:bg-slate-200 transition-colors focus:outline-none"
                                id="notification-bell">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                                <span id="notification-badge"
                                    class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white hidden">0</span>
                            </button>
                            @include('components.notification-dropdown')
                        </div>
                    @endauth
                </div>
            </header>

            @if(session('success'))
                <div class="cu-container pt-4">
                    <div class="cu-alert-success" role="status">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="cu-container pt-4">
                    <div class="cu-alert-error" role="alert">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <main class="cu-main">
                <div class="cu-container">
                    @yield('content')
                </div>
            </main>

            <footer class="cu-footer">
                <div class="cu-footer-inner">
                    <p>&copy; {{ date('Y') }} {{ __('app.copyright') }}</p>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function toggleReportsNavDropdown() {
            const dropdown = document.getElementById('reports-nav-dropdown');
            dropdown.classList.toggle('hidden');
        }

        function toggleLeaveTypesNavDropdown() {
            const dropdown = document.getElementById('leave-types-nav-dropdown');
            dropdown.classList.toggle('hidden');
        }

        document.addEventListener('click', function (event) {
            const reportsDropdown = document.getElementById('reports-nav-dropdown');
            const leaveTypesDropdown = document.getElementById('leave-types-nav-dropdown');
            const reportsButton = event.target.closest('button[onclick="toggleReportsNavDropdown()"]');
            const leaveTypesButton = event.target.closest('button[onclick="toggleLeaveTypesNavDropdown()"]');
            if (!reportsButton && reportsDropdown && !reportsDropdown.classList.contains('hidden')) {
                reportsDropdown.classList.add('hidden');
            }
            if (!leaveTypesButton && leaveTypesDropdown && !leaveTypesDropdown.classList.contains('hidden')) {
                leaveTypesDropdown.classList.add('hidden');
            }
        });
    </script>
    @stack('scripts')
    @include('components.confirm-modal')
    @include('components.alert-modal')
</body>

</html>
