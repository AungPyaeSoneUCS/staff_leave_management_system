<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.reset_title') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="cu-auth-page">
    <div class="cu-auth-card">
        <div class="cu-auth-logo">
            <img src="{{ asset('images/ucsh_logo.jpg') }}" class="overflow-hidden" />
        </div>
        <h1 class="cu-auth-title">{{ __('auth.reset_heading') }}</h1>
        <p class="cu-auth-subtitle">{{ __('auth.reset_subtitle') }}</p>

        <form class="mt-8 space-y-5" method="POST" action="{{ route('password.reset') }}">
            @csrf
            <div>
                <label for="password" class="cu-label">{{ __('auth.new_password') }}</label>
                <div class="flex justify-center items-center">
                            <input id="password" type="password" name="password" required class="cu-input">
                            <span id="eye-open-password" class="px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                            <span id="eye-close-password" class="hidden px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </span>
                                        @error('password')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="cu-label">{{ __('auth.confirm_password') }}</label>
                <div class="flex justify-center items-center">
                            <input id="password_confirmation" type="password" name="password_confirmation" required class="cu-input">
                            <span id="eye-open-password_confirmation" class="px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                            <span id="eye-close-password_confirmation" class="hidden px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </span>
                                        @error('password_confirmation')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="cu-btn-primary w-full py-3">
                {{ __('auth.reset_button') }}
            </button>
        </form>
    </div>
</body>
</html>
