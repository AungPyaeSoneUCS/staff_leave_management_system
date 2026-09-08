<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $admin) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $texts = config('mail_texts.admin_created', []);

        return (new MailMessage)
            ->subject($texts['subject'] ?? __('notifications.subject_admin_created'))
            ->markdown('emails.admin-created', [
                'admin' => $this->admin,
                'texts' => $texts,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => config('mail_texts.admin_created.title', __('notifications.title_admin_created')),
            'message' => config('mail_texts.admin_created.body') ? str_replace(':name', $this->admin->name, config('mail_texts.admin_created.body')) : __('notifications.admin_created_message', ['name' => $this->admin->name]),
            'url' => route('super-admin.admins.index'),
        ];
    }
}
