<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DollarRateNotification extends Notification
{
    use Queueable;

    protected $rate;

    public function __construct($rate)
    {
        $this->rate = $rate;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Dollar Price Today',
            'message' => " Dollar Price Now : $this->rate",
        ];
    }
}
