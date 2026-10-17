@component('mail::message')
# {{ $texts['title'] }}

{{ str_replace(':name', $name, $texts['intro']) }}

@component('mail::table')
| {{ $texts['account_label'] }} | {{ $texts['action_label'] }} | {{ $texts['time_label'] }} | {{ $texts['ip_label'] }} |
|:--|:--|:--|:--|
| {{ $name }} | {{ $texts['action_value'] }} | {{ $time }} | {{ $ip }} |
@endcomponent

{{ $texts['outro'] }}

@lang('Thanks'),
{{ $texts['signature'] }}
@endcomponent
