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
        try {
            $response = $client->chat()->create([
                                                    'model' => config('bot.openai_model'),
                                                    'messages' => [
                                                        ['role' => 'user', 'content' => $text],
                                                    ],
                                                ]);
            return $response->choices[0]->message->content;
        } catch (\OpenAI\Exceptions\ErrorException $e) {
            \App\Actions\OpenAIErrorHandler::handle($e);
            return 'Ась?';
        } catch (\Exception $e) {
            return 'Ась?';
        }
    }
}
