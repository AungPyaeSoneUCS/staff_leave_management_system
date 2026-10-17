@component('mail::message')
# {{ __('auth.otp_email_heading') }}

{{ __('auth.otp_email_body') }}

@component('mail::panel')
<div style="text-align: center; font-size: 30px; letter-spacing: 8px; font-weight: 700; color: #146d6b;">
{{ $otp }}
</div>
@endcomponent

{{ __('auth.otp_email_expires') }}

@lang('Thanks'),
{{ config('app.name') }}
@endcomponent
