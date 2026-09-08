<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $admin, public ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $texts = config('mail_texts.admin_assigned', []);

        return (new MailMessage)
            ->subject($texts['subject'] ?? __('notifications.subject_assigned'))
            ->markdown('emails.admin-assigned', [
                'admin' => $this->admin,
                'reason' => $this->reason,
                'texts' => $texts,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => config('mail_texts.admin_assigned.title', __('notifications.title_assigned')),
            'message' => config('mail_texts.admin_assigned.body', __('notifications.assigned_message')),
            'url' => route('admin.dashboard'),
        ];
    }
}
