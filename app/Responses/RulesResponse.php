<?php

namespace App\Responses;


use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class RulesResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        $text = 'В этом чате пока что анархия наскорлько я знаю.';
        if ($chat->rules) {
            $text = 'Правила чата: '.PHP_EOL.PHP_EOL.$chat->rules;
        }
        return $text;
    }
}
