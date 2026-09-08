@component('mail::message')
# {{ $texts['subject'] }}

{{ str_replace(':name', $recipientName ?? $leaveRequest->user->name, $texts['greeting']) }}

{{ $texts['intro'] }}

@component('mail::table')
| {{ $texts['staff_name'] }} | {{ $texts['leave_type'] }} | {{ $texts['duration'] }} | {{ $texts['dates'] }} |
|--------------|--------------|--------------|--------------|
 | {{ $staffName ?? $leaveRequest->user->name }} | {{ $leaveTypeName ?? $leaveRequest->leaveType->name }} | {{ $leaveRequest->leaveType->is_not_limited ? '-' : ($leaveRequest->is_half_day ? __('common.half_day') : my_number($leaveRequest->total_days) . ' ' . __('common.days')) }} | {{\App\Support\MyanmarDateFormatter::format($leaveRequest->start_date, 'F d, Y')}} — {{ $leaveRequest->end_date ? \App\Support\MyanmarDateFormatter::format($leaveRequest->end_date, 'F d, Y') : __('common.unlimited') }} |
@endcomponent

@component('mail::button', ['url' => $url ?? route('staff.leave-requests.show', $leaveRequest)])
{{ $texts['action'] }}
@endcomponent

{{ $texts['reason'] }}: {{ $leaveRequest->reason }}

{{ $texts['outro'] }}<br>
{{ $texts['signature'] }}
@endcomponent
