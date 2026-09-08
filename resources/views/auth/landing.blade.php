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
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(15,118,110,0.22),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(20,184,166,0.14),transparent_30%),linear-gradient(180deg,#f8fbfa_0%,#edf5f3_100%)]"></div>
        <div class="absolute bottom-[-8rem] left-[-5rem] h-96 w-96 rounded-full bg-teal-500/15 blur-3xl"></div>
        <img src="{{ asset('images/university_image.png') }}" alt="University campus" class="absolute inset-0 h-full w-full object-cover opacity-[0.18] mix-blend-multiply">
    </div>

    <div class="relative flex min-h-screen flex-col">
        <header class="border-b border-white/60 bg-white/70 backdrop-blur-xl shadow-[0_8px_30px_rgba(15,23,42,0.06)]">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-4 gap-y-3 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-teal-700/15 sm:h-14 sm:w-14">
                        <img src="{{ asset('images/ucsh_logo.jpg') }}" alt="University logo" class="h-full w-full object-contain p-1.5">
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-base font-black tracking-tight text-slate-900 sm:text-lg lg:text-2xl">{{ __('landing.system_name') }}</p>
                        <h1 class="hidden text-xs font-semibold uppercase tracking-[0.24em] text-teal-700 p-2 sm:block">{{ __('landing.university_name') }}</h1>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                    <div class="flex items-center rounded-full border border-teal-700/10 bg-white/80 p-1 shadow-sm">
                        <a href="{{ route('lang.switch', 'en') }}" class="rounded-full px-2.5 py-1.5 text-xs font-bold transition sm:px-3 {{ session('locale', 'en') === 'en' ? 'bg-teal-700 text-white shadow' : 'text-slate-600 hover:text-teal-800' }}">{{ __('nav.english') }}</a>
                        <a href="{{ route('lang.switch', 'my') }}" class="rounded-full px-2.5 py-1.5 text-xs font-bold transition sm:px-3 {{ session('locale') === 'my' ? 'bg-teal-700 text-white shadow' : 'text-slate-600 hover:text-teal-800' }}">{{ __('nav.myanmar') }}</a>
                    </div>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-teal-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-teal-800/15 transition hover:bg-teal-800 hover:shadow-teal-800/25 sm:px-5">
                        {{ __('auth.sign_in_button') }}
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-10 sm:px-6 sm:py-12 lg:px-8 lg:py-16">
            <div class="lg:mt-8">
                <section class="grid gap-10 lg:grid-cols-12 lg:items-center">
                    <div class="flex flex-col items-start space-y-6 lg:col-span-7">
                        <div class="inline-flex items-center gap-2 rounded-full border border-teal-700/10 bg-white/80 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-teal-800 shadow-sm backdrop-blur sm:px-4 sm:py-2 sm:text-[15px] lg:text-[23px] lg:tracking-[0.22em]">
                            {{ __('landing.system_name') }}
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-2xl font-black leading-tight tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                                {{ __('landing.university_name') }}
                            </h2>
                            <p class="text-sm leading-relaxed text-slate-700 sm:text-base lg:text-lg">
                                {{ __('landing.available_leave') }}
                            </p>
                        </div>

                        <div class="w-full pt-2 sm:w-auto">
                            <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-full bg-gradient-to-r from-teal-700 to-emerald-700 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-teal-800/20 transition hover:-translate-y-0.5 hover:from-teal-800 hover:to-emerald-800 sm:w-auto">
                                {{ __('auth.sign_in_title') }}
                            </a>
                        </div>
                    </div>

                    <div class="flex justify-center lg:col-span-5 lg:justify-end">
                        <div class="relative flex h-56 w-56 items-center justify-center rounded-3xl border border-white/80 bg-white/60 p-4 shadow-2xl shadow-teal-900/10 backdrop-blur-xl ring-1 ring-teal-700/10 sm:h-80 sm:w-80 sm:p-6">
                            <div class="absolute inset-0 -z-10 rounded-3xl bg-gradient-to-br from-teal-500/10 to-emerald-500/10 blur-xl"></div>
                            <img src="{{ asset('images/university.png') }}" alt="University Logo" class="h-full w-full object-cover rounded-3xl drop-shadow-md">
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>