<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.sign_in_title') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f3f7f6] text-slate-900 antialiased">
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <img src="{{ asset('images/university_image.png') }}" alt="University campus" class="absolute inset-0 h-full w-full object-cover mix-blend-multiply">
    </div>

    <div class="relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8 opacity-97">
        <div class="w-full max-w-3xl rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-[0_20px_60px_rgba(15,23,42,0.14)] backdrop-blur-xl sm:p-8 lg:p-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-8">
                <div class="lg:w-5/12">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-slate-700 transition hover:text-teal-800">
                        <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-teal-700/15">
                            <img src="{{ asset('images/ucsh_logo.jpg') }}" alt="University logo" class="h-full w-full object-contain p-1.5">
                        </span>
                        <span class="text-sm font-bold uppercase tracking-[0.18em]">{{ __('landing.system_name') }}</span>
                    </a>

                    <div class="mt-6 space-y-3">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-700">{{ __('auth.sign_in_title') }}</p>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('auth.sign_in_heading') }}</h1>
                        <p class="text-sm leading-6 text-slate-700">
                            {{ __('auth.select_role_n_login') }}
                        </p>
                    </div>
                </div>

                <div class="lg:w-7/12">
                    @if(session('status'))
                        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-900" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(isset($errors) && method_exists($errors, 'any') && $errors->any())
                        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800" role="alert">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form class="space-y-5" action="{{ route('login.store') }}" method="POST">
                        @csrf

                        <div>
                            <label for="role" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.22em] text-slate-700">{{ __('auth.select_role') }}</label>
                            <select id="role" name="role" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                                <option value="staff" @selected(old('role') === 'staff')>{{ __('common.role.staff') }}</option>
                                <option value="department_head" @selected(old('role') === 'department_head')>{{ __('common.role.department_head') }}</option>
                                <option value="super_admin" @selected(old('role') === 'super_admin')>{{ __('common.role.super_admin') }}</option>
                                <option value="admin" @selected(old('role') === 'admin')>{{ __('common.role.admin') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.22em] text-slate-700">{{ __('auth.email_label') }}</label>
                            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" placeholder="{{ __('common.email_placeholder') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                            @error('email')
                                <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.22em] text-slate-700">{{ __('auth.password_label') }}</label>
                            <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="{{ __('common.password_placeholder') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                            @error('password')
                                <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <label class="flex items-center gap-2.5 text-sm font-semibold text-slate-700">
                                <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-700/20" @checked(old('remember'))>
                                {{ __('auth.remember_me') }}
                            </label>
                            <a href="{{ route('password.forgot') }}" class="text-sm font-bold text-teal-800 transition hover:text-teal-950">
                                {{ __('auth.forgot_password_link') }}
                            </a>
                        </div>

                        <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-700 px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-teal-800/20 transition hover:from-teal-800 hover:to-emerald-800 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                            {{ __('auth.sign_in_button') }}
                        </button>

                        <a href="{{ url('/') }}" class="block text-center text-sm font-bold text-slate-600 transition hover:text-teal-800">
                            {{ __('common.back') }}
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>