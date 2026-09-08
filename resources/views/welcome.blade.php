<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('welcome.title') }}</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="cu-hero">
    <header class="w-full max-w-6xl mx-auto px-6 py-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25 text-white font-bold text-sm">CU</span>
            <div>
                <p class="text-white font-bold tracking-tight">{{ __('app.university') }}</p>
                <p class="text-primary-200/80 text-xs uppercase tracking-wider">{{ __('app.name') }}</p>
            </div>
        </div>
        <nav class="flex items-center gap-3">
            @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex px-5 py-2 rounded-xl text-sm font-semibold text-slate-900 bg-white hover:bg-primary-50 transition-colors">
                    {{ __('welcome.open_dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex px-5 py-2 rounded-xl text-sm font-semibold text-white border border-white/30 hover:bg-white/10 transition-colors">
                    {{ __('welcome.sign_in') }}
                </a>
            @endauth
        </nav>
    </header>

    <main class="flex-1 flex items-center justify-center px-6 pb-16">
        <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-10 items-center">
            <div class="text-white space-y-6">
                <span class="inline-flex items-center rounded-full bg-primary-400/20 text-primary-100 text-xs font-semibold px-3 py-1 ring-1 ring-primary-300/30">
                    {{ __('welcome.modern_workforce_tools') }}
                </span>
                <h1 class="text-4xl sm:text-5xl font-bold tracking-tight leading-tight">
                    {{ __('welcome.leave_management') }}<br>
                    <span class="text-primary-300">{{ __('welcome.built_for_cu') }}</span>
                </h1>
                <p class="text-lg text-primary-100/80 max-w-lg leading-relaxed">
                    {{ __('welcome.subtitle') }}
                </p>
                <div class="flex flex-wrap gap-3 pt-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="cu-btn-primary">{{ __('welcome.go_to_dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="cu-btn-primary">{{ __('welcome.staff_sign_in') }}</a>
                    @endauth
                </div>
            </div>

            <div class="cu-hero-card space-y-6">
                <h2 class="text-xl font-semibold">{{ __('welcome.platform_highlights') }}</h2>
                <ul class="space-y-4 text-sm text-white/85">
                    <li class="flex gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary-400/30 text-primary-100 text-xs font-bold">1</span>
                        <div>
                            <p class="font-semibold text-white">{{ __('welcome.role_based_workflows') }}</p>
                            <p class="text-white/70">{{ __('welcome.role_based_desc') }}</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary-400/30 text-primary-100 text-xs font-bold">2</span>
                        <div>
                            <p class="font-semibold text-white">{{ __('welcome.live_leave_balances') }}</p>
                            <p class="text-white/70">{{ __('welcome.live_balance_desc') }}</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary-400/30 text-primary-100 text-xs font-bold">3</span>
                        <div>
                            <p class="font-semibold text-white">{{ __('welcome.institution_branding') }}</p>
                            <p class="text-white/70">{{ __('welcome.branding_desc') }}</p>
                        </div>
                    </li>
                </ul>
                <p class="text-xs text-white/50 pt-2 border-t border-white/10">
                    {{ __('app.system_footer') }}
                </p>
            </div>
        </div>
    </main>
</body>
</html>
