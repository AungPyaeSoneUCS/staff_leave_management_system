<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountActivityMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $action,
        public string $ip,
        public string $time,
    ) {
    }

    public function build()
    {
        $key = $this->action === 'logout' ? 'account_logout' : 'account_login';
        $texts = config("mail_texts.{$key}");

        return $this->subject($texts['subject'])
            ->markdown('emails.account-activity')
            ->with([
                'texts' => $texts,
                'name' => $this->name,
            ]);
    }
}
