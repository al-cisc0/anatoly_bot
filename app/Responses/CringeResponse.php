<?php

namespace App\Responses;

use App\CrawlerExtracts\PickupMasterExtract;
use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class CringeResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        return PickupMasterExtract::getExtract();
    }
}
