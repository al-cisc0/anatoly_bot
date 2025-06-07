<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SendBotResponseAction
{
    public static function execute(User $user, array $message, Notification $notification): void
    {
        try {
            $user->chat_id = $message['chat']['id'];
            $user->notify($notification);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}

