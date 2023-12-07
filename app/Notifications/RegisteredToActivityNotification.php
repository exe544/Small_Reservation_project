<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisteredToActivityNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Activity $activity)
    {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('You have successfully registered')
                    ->line('Thank you for registering to the activity ' . $this->activity->name)
                    ->line('Start time: ' . $this->activity->start_date)
                    ->line('Your guide email: ' . $this->activity->guide()->email);

    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
