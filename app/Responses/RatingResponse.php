<?php

namespace App\Responses;

use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class RatingResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        return 'Твой рейтинг '.$user->chats()->where('id',$chat->id)->first()?->pivot->rating;
    }
}
