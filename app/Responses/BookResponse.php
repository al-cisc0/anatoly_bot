<?php

namespace App\Responses;


use App\CrawlerExtracts\BooksExtract;
use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class BookResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        return BooksExtract::getExtract();
    }
}
