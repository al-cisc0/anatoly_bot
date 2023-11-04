<?php

namespace App\Responses;


use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class DonationResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        return 'Помочь на содержание и развитие Анатолия можно по реквизитам: '.PHP_EOL.PHP_EOL.config('bot.donation_address');
    }
}
