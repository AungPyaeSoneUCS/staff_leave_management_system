<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('auth.otp_email_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h1>{{ __('auth.otp_email_heading') }}</h1>
    <p>{{ __('auth.otp_email_body') }}</p>
    <div style="background: #f3f4f6; border: 1px solid #d1d5db; padding: 16px; border-radius: 8px; text-align: center; font-size: 24px; letter-spacing: 4px; font-weight: bold;">
        {{ $otp }}
    </div>
    <p>{{ __('auth.otp_email_expires') }}</p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
