<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// NOTE: This notification is delivered synchronously (not queued). If mail
// volume grows, implement Illuminate\Contracts\Queue\ShouldQueue here to push
// delivery onto a queue (the Queueable trait is already in use).
class SystemNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  string       $title    Short heading for the notification.
     * @param  string       $message  Body / description text.
     * @param  string|null  $url      Optional link to open when the notification is clicked.
     * @param  string       $icon     Icon key (used by the view to pick an SVG). Defaults to "bell".
     */
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $icon = 'bell',
    ) {
    }

    /**
     * Delivery channels. Always "database"; additionally "mail" only when
     * email notifications are explicitly enabled via config('hr.email_notifications').
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('hr.email_notifications')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Build the email representation of the notification.
     *
     * Only used when the "mail" channel is active (see via()).
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject($this->title)
            ->line($this->message);

        if ($this->url !== null && $this->url !== '') {
            // Build an absolute URL for the action button.
            $mail->action('View Details', url($this->url));
        }

        return $mail;
    }

    /**
     * Payload stored in the notifications.data column.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
            'icon'    => $this->icon,
        ];
    }
}
