<?php

namespace App\Responses;


use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class ChatGPTResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        $text = mb_strtolower($message['text'] ?? '');
        $text = str_replace($reaction->key_phrase,'',$text);
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
        return $message;
    }
}
