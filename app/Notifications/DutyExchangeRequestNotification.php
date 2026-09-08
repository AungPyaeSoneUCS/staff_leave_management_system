<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\MyanmarDateFormatter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DutyExchangeRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $texts = config('mail_texts.duty_exchange_request', config('mail_texts.leave_request_submitted'));

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
                'staffName' => $staffName,
                'leaveTypeName' => $leaveTypeName,
                'url' => route('duty-exchange.show', $this->leaveRequest, false),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $locale = app()->getLocale();
        $staffName = $locale == 'my' ? ($this->leaveRequest->user->name_mm ?? $this->leaveRequest->user->name) : $this->leaveRequest->user->name;
        $leaveTypeName = $locale == 'my' ? ($this->leaveRequest->leaveType->name_mm ?? $this->leaveRequest->leaveType->name) : $this->leaveRequest->leaveType->name;

        return [
            'title' => __('notifications.duty_exchange_requested'),
            'message' => __('notifications.duty_exchange_request_message', [
                'user' => $staffName,
                'days' => $this->leaveRequest->total_days,
                'start_date' => MyanmarDateFormatter::format($this->leaveRequest->start_date, 'F d, Y'),
            ]),
            'url' => route('duty-exchange.show', $this->leaveRequest, false),
            'leave_request_id' => $this->leaveRequest->id,
            'submitted_by' => $staffName,
            'leave_type' => $leaveTypeName,
            'total_days' => $this->leaveRequest->total_days,
            'start_date' => MyanmarDateFormatter::format($this->leaveRequest->start_date, 'F d, Y'),
            'end_date' => MyanmarDateFormatter::format($this->leaveRequest->end_date, 'F d, Y'),
            'status' => 'duty_exchange_pending',
        ];
    }
}