<?php

namespace App\Actions;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class ApproveChatJoinRequestAction
{
    public static function execute(int $chatId, int $userId): void
    {
        try {
            $telegram = new Api(config('services.telegram-bot-api.token'));
            $telegram->approveChatJoinRequest([
                'chat_id' => $chatId,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}

