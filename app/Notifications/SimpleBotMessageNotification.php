<?php

namespace App\Notifications;

use App\Telegram\CustomTelegramMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class SimpleBotMessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $content
     * @param array $messageArray
     */
    public function __construct(
        protected string $content,
        protected array $messageArray,
    )
    {

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ["telegram"];
    }

    public function toTelegram($notifiable)
    {
        $notification = CustomTelegramMessage::create(
            $this->content,
            $this->messageArray['message_id']
        )->to($notifiable->chat_id);
        return $notification;
    }

}
