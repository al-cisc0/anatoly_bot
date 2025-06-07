<?php

namespace App\Actions;

use App\Models\Chat;
use App\Models\User;
use App\Notifications\SimpleBotMessageNotification;
use Telegram\Bot\Api;

class CheckIfReadOnlyAction
{
    public static function execute(User $user, Chat $currentChat, int $rating, Chat $chat, array $message): void
    {
        if ($rating < config('bot.read_only_rating') && !$currentChat->pivot->is_readonly) {
            $telegram = new Api(config('services.telegram-bot-api.token'));
            $permissions = [
                'can_send_messages' => false,
                'can_send_media_messages' => false,
                'can_send_polls' => false,
                'can_send_other_messages' => false,
                'can_add_web_page_previews' => false,
                'can_change_info' => false,
                'can_invite_users' => false,
                'can_pin_messages' => false
            ];
            $telegram->restrictChatMember([
                'chat_id' => $chat->chat_id,
                'user_id' => $user->telegram_id,
                'permissions' => $permissions
            ]);
            SendBotResponseAction::execute(
                $user,
                $message,
                new SimpleBotMessageNotification(
                    'Похоже, что ' . $user->name . ' слишком раздражает участников чата. Он получил ридонли.',
                    $message
                )
            );
            $currentChat->pivot->is_readonly = 1;
            $currentChat->pivot->save();
        }
    }
}

