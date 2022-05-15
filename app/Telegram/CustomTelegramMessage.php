<?php

namespace App\Telegram;

class CustomTelegramMessage extends \NotificationChannels\Telegram\TelegramMessage
{
    public function __construct(
        string $content = '',
        string $messageId = '',
    )
    {
        $this->content($content);
        $this->payload['parse_mode'] = 'Markdown';
        $this->payload['reply_to_message_id'] = $messageId;
    }

    public static function create(
        string $content = '',
        string $messageId = '',
    ): self
    {
        return new self($content,$messageId);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
