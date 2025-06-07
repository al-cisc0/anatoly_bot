<?php

namespace App\Actions;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class BanTelegramUserAction
{
    public static function execute(array $message): void
    {
        try {
            $telegram = new Api(config('services.telegram-bot-api.token'));
            $telegram->banChatMember([
                'chat_id'         => $message['chat']['id'],
                'user_id'         => $message['from']['id'],
                'revoke_messages' => true
            ]);
            $telegram->deleteMessage([
                'chat_id'    => $message['chat']['id'],
                'message_id' => $message['message_id']
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}

