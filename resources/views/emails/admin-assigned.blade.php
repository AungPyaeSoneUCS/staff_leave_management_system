@component('mail::message')
# {{ $texts['title'] }}

{{ str_replace(':name', $admin->name, $texts['greeting']) }}

{{ $texts['intro'] }}

@component('mail::panel')
{{ $texts['reason_label'] }}: {{ $reason ?: '—' }}
@endcomponent

@component('mail::button', ['url' => route('admin.dashboard')])
{{ $texts['action'] }}
@endcomponent

{{ $texts['outro'] }}<br>
{{ $texts['signature'] }}
@endcomponent
