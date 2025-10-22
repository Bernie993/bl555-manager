<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $notification;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[BL555] ' . $this->notification->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'notification' => $this->notification,
                'actionUrl' => $this->getActionUrl(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get action URL for notification
     */
    private function getActionUrl(): string
    {
        $baseUrl = config('app.url');
        
        if (!$this->notification->notifiable_type || !$this->notification->notifiable_id) {
            return $baseUrl;
        }

        return match($this->notification->notifiable_type) {
            'App\Models\Service' => $baseUrl . '/services/' . $this->notification->notifiable_id,
            'App\Models\ServiceProposal' => $baseUrl . '/service-proposals/' . $this->notification->notifiable_id,
            default => $baseUrl,
        };
    }
}