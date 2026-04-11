<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class SimpleBotBroadcastNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $content,
        protected int $chatId,
    ) {
    }

    public function via($notifiable)
    {
        return ["telegram"];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->content($this->content . "\n\n" . '@wise_anatoly_support')
            ->to($this->chatId)
            ->options([
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
    }
}