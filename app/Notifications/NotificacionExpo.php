<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Expo\ExpoChannel;
use NotificationChannels\Expo\ExpoMessage;

class NotificacionExpo extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $titulo) {}

    public function via($notifiable): array
    {
        return [ExpoChannel::class];
    }

    public function toExpo($notifiable): ExpoMessage
    {
        return ExpoMessage::create("Nuevo Evento")
            ->body($this->titulo)
            ->priority('high');
    }
}
