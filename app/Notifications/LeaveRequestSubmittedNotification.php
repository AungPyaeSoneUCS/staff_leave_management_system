<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\MyanmarDateFormatter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest, public string $recipientType = 'department_head') {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $configKey = match ($this->recipientType) {
            'staff' => 'leave_request_submitted',
            'department_head' => 'leave_request_submitted_dept_head',
            'admin' => 'leave_request_submitted_admin',
            'super_admin' => 'leave_request_submitted_super_admin',
            default => 'leave_request_submitted',
        };

        $texts = config("mail_texts.{$configKey}", config('mail_texts.leave_request_submitted'));

        $url = match ($this->recipientType) {
            'staff' => route('staff.leave-requests.show', $this->leaveRequest, false),
            'department_head' => route('department-head.approvals.show', $this->leaveRequest, false),
            'admin' => route('central-admin.approvals.show', $this->leaveRequest, false),
            'super_admin' => route('central-admin.approvals.show', $this->leaveRequest, false),
            default => route('staff.leave-requests.show', $this->leaveRequest, false),
        };

        $locale = app()->getLocale();
        $staffName = $locale == 'my' ? ($this->leaveRequest->user->name_mm ?? $this->leaveRequest->user->name) : $this->leaveRequest->user->name;
        $leaveTypeName = $locale == 'my' ? ($this->leaveRequest->leaveType->name_mm ?? $this->leaveRequest->leaveType->name) : $this->leaveRequest->leaveType->name;
        $recipientName = $locale == 'my' ? ($notifiable->name_mm ?? $notifiable->name) : $notifiable->name;

        return (new MailMessage)
            ->subject($texts['subject'])
            ->markdown('emails.leave-request-submitted', [
                'leaveRequest' => $this->leaveRequest,
                'texts' => $texts,
                'recipientName' => $recipientName,
                'url' => $url,
                'recipientType' => $this->recipientType,
                'staffName' => $staffName,
                'leaveTypeName' => $leaveTypeName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $url = match ($this->recipientType) {
            'staff' => route('staff.leave-requests.show', $this->leaveRequest, false),
            'department_head' => route('department-head.approvals.show', $this->leaveRequest, false),
            'admin' => route('central-admin.approvals.show', $this->leaveRequest, false),
            'super_admin' => route('central-admin.approvals.show', $this->leaveRequest, false),
            default => route('staff.leave-requests.show', $this->leaveRequest, false),
        };

        $locale = app()->getLocale();
        $staffName = $locale == 'my' ? ($this->leaveRequest->user->name_mm ?? $this->leaveRequest->user->name) : $this->leaveRequest->user->name;
        $leaveTypeName = $locale == 'my' ? ($this->leaveRequest->leaveType->name_mm ?? $this->leaveRequest->leaveType->name) : $this->leaveRequest->leaveType->name;

        return [
            'title' => __('notifications.leave_request_submitted'),
            'message' => __('notifications.new_request_message', [
                'user' => $staffName,
                'days' => $this->leaveRequest->total_days,
                'start_date' => MyanmarDateFormatter::format($this->leaveRequest->start_date, 'F d, Y'),
            ]),
            'url' => $url,
            'leave_request_id' => $this->leaveRequest->id,
            'submitted_by' => $staffName,
            'leave_type' => $leaveTypeName,
            'total_days' => $this->leaveRequest->total_days,
            'start_date' => MyanmarDateFormatter::format($this->leaveRequest->start_date, 'F d, Y'),
            'end_date' => MyanmarDateFormatter::format($this->leaveRequest->end_date, 'F d, Y'),
        ];
    }
}
