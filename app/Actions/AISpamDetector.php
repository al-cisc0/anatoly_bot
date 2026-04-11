<?php

namespace App\Actions;

class AISpamDetector
{
    public static function detectSpam(
        string $message,
        $client = null
    ): int
    {
        $text = 'Проанализируй это сообщение на предмет спама. Дай свою оценку в процентах от 0 до 100. '.
            '0 - это совсем не спам, 100 - это совершенно точно спам.'.
            'При анализе учитывай только явные намерения пользователя что-то продать, купить, предложить работы,'.
            'услуги, знакомства, заработок, инвестиции, рекламу, привлечение внимания к своему профилю или каналу,'.
            'пользователи могут шутить, материться и использовать всякие оскорбительные слова или писать сообщения'.
            ' непонятного содержания. Это не спам. Если сообщение слишком короткое чтобы явно понять намерения'.
            ' пользователя то это скорее всего не спам. Ставь оценку исключительно исходя из намерений пользователя.'.
            ' Кроме того проверь не является ли сообщение пользователя известным мемом.'.
            'Напиши только цифру в ответе. '.
            'Сообщение пользователя: ' . $message;
        if (!$client) {
            $client = \OpenAI::client(config('bot.openapi_token'));
        }
        try {
            $response = $client->chat()->create([
                                                    'model' => config('bot.openai_model'),
                                                    'messages' => [
                                                        ['role' => 'user', 'content' => $text],
                                                    ],
                                                ]);
            $rating = $response->choices[0]->message->content;
        } catch (\OpenAI\Exceptions\ErrorException $e) {
            OpenAIErrorHandler::handle($e);
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
        return (int) $rating;
    }
}
