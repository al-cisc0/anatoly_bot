<?php

namespace App\Responses;

use App\CrawlerExtracts\ChapayExtract;
use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class FunnyStoryResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        return ChapayExtract::getExtract();
    }
}
