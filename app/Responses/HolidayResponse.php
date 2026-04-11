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
        $date = now()->translatedFormat('j F Y');
        $text = "Сегодня {$date}. Вспомни какие реальные необычные или нелепые праздники (в том числе неофициальные) отмечаются в эту дату в мире. Выбери один самый нелепый. Если не знаешь ни одного настоящего — выдумай сам максимально абсурдный праздник для этой даты. Целевая аудитория — взрослые люди, но не упоминай это. Без форматирования и лишних слов. Ответ строго в формате:\nНАЗВАНИЕ: название праздника\nОПИСАНИЕ: смешное описание на 3-4 предложения с юмором и иронией\nРЕАЛЬНЫЙ: да/нет";
        $client = \OpenAI::client(config('bot.openapi_token'));
        $response = $client->chat()->create([
                                                'model' => config('bot.openai_model'),
                                                'messages' => [
                                                    ['role' => 'user', 'content' => $text],
                                                ],
                                            ]);
        try {
            $content = $response->choices[0]->message->content;
        } catch (\Exception $e) {
            return 'Ась?';
        }

        preg_match('/НАЗВАНИЕ:\s*(.+)/u', $content, $nameMatch);
        preg_match('/ОПИСАНИЕ:\s*(.+)/u', $content, $descMatch);
        preg_match('/РЕАЛЬНЫЙ:\s*(.+)/u', $content, $realMatch);

        $name = trim($nameMatch[1] ?? '');
        $description = trim($descMatch[1] ?? '');
        $isReal = mb_stripos($realMatch[1] ?? '', 'да') !== false;

        if (!$name || !$description) {
            return 'Предлагаю сегодня отметить ' . $content;
        }

        $result = "Предлагаю сегодня отметить {$name}\n\n{$description}";

        if ($isReal) {
            $query = urlencode($name);
            $result .= "\n\nНе веришь? Проверь: https://www.google.com/search?q={$query}";
        }

        return $result;
    }
}
