<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.otp_title') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="cu-auth-page">
    <div class="cu-auth-card">
        <div class="cu-auth-logo">
            <img src="{{ asset('images/ucsh_logo.jpg') }}" class="w-full h-full object-contain" />
        </div>
        <h1 class="cu-auth-title">{{ __('auth.otp_heading') }}</h1>
        <p class="cu-auth-subtitle">{{ __('auth.otp_subtitle') }}</p>

        @if(session('status'))
            <div class="cu-alert-success mt-6" role="alert">
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="cu-alert-error mt-6" role="alert">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="mt-8 space-y-5" action="{{ route('password.verify.otp') }}" method="POST">
            @csrf
            <div>
                <label for="otp" class="cu-label">{{ __('auth.otp_label') }}</label>
                <input id="otp" name="otp" type="text" maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required
                    class="cu-input text-center tracking-widest text-lg"
                    placeholder="000000">
                @error('otp')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="cu-btn-primary w-full py-3">
                {{ __('auth.verify_otp_button') }}
            </button>
            <a href="{{ route('login') }}" class="cu-link block text-center text-sm">
                {{ __('auth.back_to_sign_in') }}
            </a>
        </form>
    </div>
</body>
</html>
