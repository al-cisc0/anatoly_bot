<?php

namespace App\Responses;


use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class HolidayResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        $text = 'Придумай веселый праздник который мог бы отмечаться в этот день. Во всем мире. Дай только название и описание без дополнительных слов от себя.';
        $client = \OpenAI::client(config('bot.openapi_token'));
        $response = $client->chat()->create([
                                                'model' => 'gpt-3.5-turbo',
                                                'messages' => [
                                                    ['role' => 'user', 'content' => $text],
                                                ],
                                            ]);
        try {
            $message = $response->choices[0]->message->content;
        } catch (\Exception $e) {
            $message = 'Ась?';
        }
        return 'Предлагаю сегодня отметить ' . $message;
    }
}
