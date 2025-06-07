<?php

namespace App\Actions;

use App\Notifications\SimpleBotMessageNotification;
use App\Models\Chat;
use App\Models\User;

class ProcessCaptchaAction
{
    public static function execute(Chat $chat, User $user, array $message): void
    {
        SendBotResponseAction::execute(
            $user,
            $message,
            new SimpleBotMessageNotification(
                $chat->captcha_question . ' На ответ у тебя есть 5 минут. Время пошло.',
                $message
            )
        );
    }
}

