<?php

declare(strict_types=1);

namespace App\Modules\Core\Mail;

use App\Modules\Core\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TenantNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Notification $notification,
        public readonly string $fromName = '',
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->notification->data['title'] ?? 'Notification';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'title'      => $this->notification->data['title'] ?? '',
                'body'       => $this->notification->data['body'] ?? '',
                'actionUrl'  => $this->notification->data['action_url'] ?? null,
                'actionText' => $this->notification->data['action_text'] ?? 'View',
                'fromName'   => $this->fromName,
            ],
        );
    }
}
