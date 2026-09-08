<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.super_admin_register_title') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f3f7f6] text-slate-900 antialiased">
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <img src="{{ asset('images/university_image.png') }}" alt="University campus" class="absolute inset-0 h-full w-full object-cover mix-blend-multiply">
    </div>

    <div class="relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8 opacity-97">
        <div class="w-full max-w-md rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-[0_20px_60px_rgba(15,23,42,0.14)] backdrop-blur-xl sm:p-8 lg:p-10">
            <div class="mb-6 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-teal-700/15">
                    <img src="{{ asset('images/ucsh_logo.jpg') }}" alt="University logo" class="h-full w-full object-contain p-1.5">
                </span>
                <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900">{{ __('auth.super_admin_register_title_short') }}</h1>
                <p class="mt-1 text-sm leading-6 text-slate-700">
                    {{ __('auth.super_admin_key_subtitle') }}
                </p>
            </div>

            @if(isset($errors) && method_exists($errors, 'any') && $errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800" role="alert">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form class="space-y-5" action="{{ route('super-admin.register.key') }}" method="POST">
                @csrf

                <div>
                    <label for="key" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.22em] text-slate-700">{{ __('auth.super_admin_key_label') }}</label>
                    <input id="key" name="key" type="password" required autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                    @error('key')
                        <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-700 px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-teal-800/20 transition hover:from-teal-800 hover:to-emerald-800 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                    {{ __('auth.super_admin_key_button') }}
                </button>

                <a href="{{ url('/') }}" class="block text-center text-sm font-bold text-slate-600 transition hover:text-teal-800">
                    {{ __('common.back') }}
                </a>
            </form>
        </div>
    </div>
</body>
</html>
