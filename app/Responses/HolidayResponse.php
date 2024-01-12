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
        $text = 'Придумай забавный праздник который мог бы отмечаться сегодня. Он должен быть максимально абсурдным и целевой аудиторией должны быть взрослые люди но ненадо это явно упоминать. Выбери какой-то один. Дай только название и описание без дополнительных слов от себя. Не пиши слово название и слово абсурдный.';
        $client = \OpenAI::client(config('bot.openapi_token'));
        $response = $client->chat()->create([
                                                'model' => 'gpt-4',
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
