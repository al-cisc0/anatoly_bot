<?php

namespace App\Actions;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetUserAction
{
    public static function execute(array $message): array
    {
        $chat = null;
        if (!empty($message['chat']['id'])) {
            $chat = Chat::firstOrCreate(
                [
                    'chat_id' => $message['chat']['id']
                ],
                [
                    'title' => $message['chat']['title'] ?? 'user',
                    'rules' => '',
                ]
            );
        }

        $user = null;
        if (!empty($message['from']['id'])) {
            $telegramId = $message['from']['id'];
            $userName = '';
            if (!empty($message['from']['first_name'])) {
                $userName .= $message['from']['first_name'];
            }
            if (!empty($message['from']['last_name'])) {
                $userName .= ' ' . $message['from']['last_name'];
            }
            $user = User::ofTelegramId($telegramId)->first();
            $isOwner = 0;
            $isActive = 0;
            if (config('bot.owner_id') == $telegramId) {
                $isOwner = 1;
                $isActive = 1;
            }
            if (!$user) {
                $user = User::create([
                    'telegram_id' => $telegramId,
                    'name'        => $userName,
                    'is_active'   => $isActive,
                    'is_admin'    => $isOwner,
                    'password'    => Hash::make(Str::random(8))
                ]);
            }
            if ($chat && !$user->chats()->where('id', $chat->id)->exists()) {
                $user->chats()->attach([$chat->id => ['joined_at' => now()]]);
            }
        }

        return [$user, $chat];
    }
}

