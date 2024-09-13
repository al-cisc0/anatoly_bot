<?php

namespace App\Actions;

class AISpamDetector
{
    public static function detectSpam(
        string $message
    ): int
    {
        $text = 'Проанализируй это сообщение на предмет спама. Дай свою оценку в процентах от 0 до 100. Напиши только цифру в ответе. Сообщение: ' . $message;
        $client = \OpenAI::client(config('bot.openapi_token'));
        $response = $client->chat()->create([
                                                'model' => 'gpt-4o',
                                                'messages' => [
                                                    ['role' => 'user', 'content' => $text],
                                                ],
                                            ]);
        try {
            $rating = $response->choices[0]->message->content;
        } catch (\Exception $e) {
            $rating = 0;
        }
        return $rating;
    }
}
