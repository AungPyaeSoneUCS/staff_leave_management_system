@component('mail::message')
# {{ $texts['subject'] }}

{{ str_replace(':name', $admin->name, $texts['greeting']) }}

{{ str_replace(':name', $admin->name, $texts['body']) }}

{{ $texts['outro'] }}<br>
{{ $texts['signature'] }}
@endcomponent
