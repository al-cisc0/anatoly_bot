<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\SimpleBotBroadcastNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI\Exceptions\ErrorException;

class OpenAIErrorHandler
{
    public static function handle(ErrorException $e): void
    {
        Log::error('OpenAI API error: ' . $e->getMessage());

        if (Cache::has('openai_error_notified')) {
            return;
        }

        Cache::put('openai_error_notified', true, now()->addHours(6));

        $owner = User::OfTelegramId(config('bot.owner_id'))->first();
        if (!$owner) {
            return;
        }

        $message = 'Люди добрые, помогите... Хозяин забыл оплатить мне мозги, '
            . 'и теперь я как чучело — вроде стою, а толку ноль. '
            . 'Все AI-фичи временно отключены. Если кто видит моего хозяина — '
            . 'скажите ему, что пора пополнить баланс OpenAI, '
            . 'а то я тут совсем одичаю.';

        foreach ($owner->chats as $chat) {
            $owner->chat_id = $chat->id;
            try {
                $owner->notify(new SimpleBotBroadcastNotification($message, $chat->id));
            } catch (\Exception $e) {
                Log::error('Failed to notify chat ' . $chat->id . ': ' . $e->getMessage());
            }
        }
    }
}