<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.forgot_title') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="cu-auth-page">
    <div class="cu-auth-card">
        <div class="cu-auth-logo">
            <img src="{{ asset('images/ucsh_logo.jpg') }}" class="overflow-hidden" />
        </div>
        <h1 class="cu-auth-title">{{ __('auth.forgot_heading') }}</h1>
        <p class="cu-auth-subtitle">{{ __('auth.forgot_subtitle') }}</p>

        <form class="mt-8 space-y-5" action="{{ route('password.forgot') }}" method="POST">
            @csrf
            <div>
                <label for="email" class="cu-label">{{ __('auth.email_label') }}</label>
                <input id="email" name="email" type="email" required
                    class="cu-input"
                    placeholder="{{ __('common.email_placeholder') }}">
                @error('email')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="cu-btn-primary w-full py-3">
                {{ __('auth.send_otp_button') }}
            </button>
            <a href="{{ route('login') }}" class="cu-link block text-center text-sm">
                {{ __('auth.back_to_sign_in') }}
            </a>
        </form>
    </div>
</body>
</html>
