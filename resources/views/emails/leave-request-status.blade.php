@component('mail::message')
# {{ $texts['subject'] }}

{{ str_replace(':name', $recipientName ?? $leaveRequest->user->name, $texts['greeting']) }}

{{ $texts['intro'] }}

@component('mail::table')
| {{ $texts['leave_type'] }} | {{ $texts['duration'] }} | {{ $texts['dates'] }} | {{ $texts['status'] }} |
|--------------|--------------|--------------|--------------|
 | {{ $leaveTypeName ?? $leaveRequest->leaveType->name }} | {{ $leaveRequest->leaveType->is_not_limited ? '-' : ($leaveRequest->is_half_day ? __('common.half_day') : my_number($leaveRequest->total_days) . ' ' . __('common.days')) }} | {{\App\Support\MyanmarDateFormatter::format($leaveRequest->start_date, 'F d, Y')}} — {{ $leaveRequest->end_date ? \App\Support\MyanmarDateFormatter::format($leaveRequest->end_date, 'F d, Y') : __('common.unlimited') }} | {{ __('common.' . $status) }} |
@endcomponent

@component('mail::button', ['url' => $url ?? route('staff.leave-requests.show', $leaveRequest)])
{{ $texts['action'] }}
@endcomponent

@if(isset($texts['remarks']))
@if($leaveRequest->reviewer_remarks)
{{ __('common.department_head') }} {{ $texts['remarks'] }}: {{ $leaveRequest->reviewer_remarks }}
@endif
@if($leaveRequest->hr_remarks)
{{ __('common.hr_central_admin') }} {{ $texts['remarks'] }}: {{ $leaveRequest->hr_remarks }}
@endif
@if($leaveRequest->super_admin_remarks)
{{ __('common.super_admin') }} {{ $texts['remarks'] }}: {{ $leaveRequest->super_admin_remarks }}
@endif
@if($leaveRequest->review_remarks && ! $leaveRequest->reviewer_remarks && ! $leaveRequest->hr_remarks && ! $leaveRequest->super_admin_remarks)
{{ $texts['remarks'] }}: {{ $leaveRequest->review_remarks }}
@endif
@endif

{{ $texts['outro'] }}<br>
{{ $texts['signature'] }}
@endcomponent
