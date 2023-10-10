<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CsvProcessingComplete extends Notification
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Your CSV file has been processed successfully.')
            ->action('View Results', url('/upload_history/index'))
            ->line('Thank you for using our service!');
    }
}
