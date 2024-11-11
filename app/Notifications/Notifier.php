<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Notifier extends Notification implements ShouldQueue
{
    use Queueable;

    private $mail;

    /**
     * Create a new notification instance.
     */
    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = new MailMessage();

        if (is_string($this->mail['subject'])) $mail->subject($this->mail['subject']);

        if (is_array($this->mail['lines']) && count($this->mail['lines']) > 0) {
            foreach ($this->mail['lines'] as $line) {
                $mail->line($line);
            }
        }

        if (is_string($this->mail['action']['title']) && is_string($this->mail['action']['url'])) {
            $mail->action($this->mail['action']['title'], $this->mail['action']['url']);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
