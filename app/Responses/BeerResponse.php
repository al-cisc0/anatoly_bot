<?php

namespace App\Responses;

use App\Models\Beer;
use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class BeerResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        return Beer::inRandomOrder()->first()->content;
    }
}
