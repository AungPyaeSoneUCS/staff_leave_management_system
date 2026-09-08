<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\MyanmarDateFormatter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest, public string $status) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subjectKey = match ($this->status) {
            'submitted' => 'leave_request_submitted',
            'approved' => 'leave_request_approved',
            'rejected' => 'leave_request_rejected',
            'pending_hr' => 'leave_request_pending_hr',
            'pending_super_admin' => 'leave_request_pending_super_admin',
            'updated' => 'leave_request_updated',
            'dept_approved' => 'dept_head_approved',
            'revoked' => 'leave_request_updated',
            default => 'leave_request_approved',
        };

        $texts = config("mail_texts.{$subjectKey}", config('mail_texts.leave_request_approved'));

        $locale = app()->getLocale();
        $staffName = $locale == 'my' ? ($this->leaveRequest->user->name_mm ?? $this->leaveRequest->user->name) : $this->leaveRequest->user->name;
        $leaveTypeName = $locale == 'my' ? ($this->leaveRequest->leaveType->name_mm ?? $this->leaveRequest->leaveType->name) : $this->leaveRequest->leaveType->name;
        $recipientName = $locale == 'my' ? ($notifiable->name_mm ?? $notifiable->name) : $notifiable->name;
        $departmentName = $this->leaveRequest->user->department?->name ?? __('common.no_department');

        if ($this->status === 'dept_approved') {
            $texts['intro'] = str_replace(
                [':staff_name', ':department_name'],
                [$staffName, $departmentName],
                $texts['intro']
            );
        }

        $url = match (true) {
            $notifiable->isStaff() => route('staff.leave-requests.show', $this->leaveRequest, false),
            $notifiable->isDepartmentHead() => route('department-head.approvals.show', $this->leaveRequest, false),
            $notifiable->isAdmin() => route('central-admin.approvals.show', $this->leaveRequest, false),
            $notifiable->isSuperAdmin() => route('central-admin.approvals.show', $this->leaveRequest, false),
            default => '#',
        };

        return (new MailMessage)
            ->subject($texts['subject'])
            ->markdown('emails.leave-request-status', [
                'leaveRequest' => $this->leaveRequest,
                'status' => $this->status,
                'texts' => $texts,
                'url' => $url,
                'recipientName' => $recipientName,
                'staffName' => $staffName,
                'leaveTypeName' => $leaveTypeName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $title = match ($this->status) {
            'submitted' => __('notifications.leave_request_submitted'),
            'approved' => __('notifications.subject_approved'),
            'rejected' => __('notifications.subject_rejected'),
            'pending_hr' => __('notifications.leave_request_forwarded_hr'),
            'pending_super_admin' => __('notifications.leave_request_forwarded_super_admin'),
            'updated' => __('notifications.leave_request_updated'),
            'dept_approved' => __('notifications.dept_head_approved_title'),
            default => __('notifications.leave_request_status_updated'),
        };

        $locale = app()->getLocale();
        $staffName = $locale == 'my' ? ($this->leaveRequest->user->name_mm ?? $this->leaveRequest->user->name) : $this->leaveRequest->user->name;
        $leaveTypeName = $locale == 'my' ? ($this->leaveRequest->leaveType->name_mm ?? $this->leaveRequest->leaveType->name) : $this->leaveRequest->leaveType->name;
        $reviewerName = $this->leaveRequest->reviewer ? ($locale == 'my' ? ($this->leaveRequest->reviewer->name_mm ?? $this->leaveRequest->reviewer->name) : $this->leaveRequest->reviewer->name) : __('common.not_assigned');
        $departmentName = $this->leaveRequest->user->department?->name ?? __('common.no_department');

        $message = match ($this->status) {
            'pending_hr' => __('notifications.pending_hr_staff_message', [
                'dept_head_name' => $reviewerName,
            ]),
            'pending_super_admin' => __('notifications.pending_super_admin_staff_message', [
                'admin_name' => $this->leaveRequest->hr ? ($locale == 'my' ? ($this->leaveRequest->hr->name_mm ?? $this->leaveRequest->hr->name) : $this->leaveRequest->hr->name) : __('common.not_assigned'),
            ]),
            'dept_approved' => __('notifications.dept_head_approved_message', [
                'dept_head_name' => $reviewerName,
                'department_name' => $departmentName,
                'staff_name' => $staffName,
            ]),
            'updated' => __('notifications.updated_message', [
                'days' => $this->leaveRequest->total_days,
                'leave_type' => $leaveTypeName,
            ]),
            default => __('notifications.status_update_message', [
                'days' => $this->leaveRequest->total_days,
                'status' => __("common.{$this->status}"),
            ]),
        };

        $url = match (true) {
            $notifiable->isStaff() => route('staff.leave-requests.show', $this->leaveRequest, false),
            $notifiable->isDepartmentHead() => route('department-head.approvals.show', $this->leaveRequest, false),
            $notifiable->isAdmin() => route('central-admin.approvals.show', $this->leaveRequest, false),
            $notifiable->isSuperAdmin() => route('central-admin.approvals.show', $this->leaveRequest, false),
            default => '#',
        };

        return [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'leave_request_id' => $this->leaveRequest->id,
            'leave_type' => $leaveTypeName,
            'total_days' => $this->leaveRequest->total_days,
            'start_date' => MyanmarDateFormatter::format($this->leaveRequest->start_date, 'F d, Y'),
            'end_date' => MyanmarDateFormatter::format($this->leaveRequest->end_date, 'F d, Y'),
            'status' => $this->status,
        ];
    }
}
