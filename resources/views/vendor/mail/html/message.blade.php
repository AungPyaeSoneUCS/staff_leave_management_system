<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.mail_url')">
<img src="{{ rtrim(config('app.mail_url'), '/') }}/images/ucsh_logo.jpg" class="logo" alt="{{ config('app.name') }}">
<span class="brand-name">{{ config('app.name') }}</span>
<span class="brand-sub">{{ __('Staff Leave Management System') }}</span>
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
