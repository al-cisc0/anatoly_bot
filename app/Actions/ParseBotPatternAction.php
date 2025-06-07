<?php

namespace App\Actions;

use App\CrawlerExtracts\BearExtract;
use App\Notifications\SimpleBotMessageNotification;
use App\Models\User;

class ParseBotPatternAction
{
    public static function execute(array $message, User $user): bool
    {
        $phrasesArray = [
            'кто тут кто тут?',
            'ммм..',
            'май год',
            'чё я несу)',
            'мда уж, пздц',
            'доказывать что то еще?лол',
            'правильно',
            'да конечно',
            'кто с рашки?',
            'мск есть?',
            'ищу рабочую силу(нет)',
            'тухло тут',
            'приручу кобанчика',
            'нужен сильный мужчина',
            'нету интереса..',
            'нужен крепкий мужчина',
            'питер тут?',
            'я из мск и англии одновремеnno',
            'что уж',
            'не стесняюсь кстати',
            'о май годнес..',
            'приветики)',
            'оке',
            'пиши если кобан)',
            'неинтересно вообще)',
            'ха)',
            'гив ми плиз',
            'покошмарю тебя)',
            'ага',
            'Да уж...',
            'Капец короче',
            'Да ну',
            'Дауж',
            'Мдаааа',
        ];
        $text = mb_strtolower($message['text'] ?? '');
        if (
            in_array($text, $phrasesArray) ||
            str_contains($text, 'мальчик') ||
            str_contains($text, 'мальчишк') ||
            str_contains($text, 'парн') ||
            str_contains($text, 'пообща') ||
            str_contains($text, 'поболта') ||
            str_contains($text, 'познаком') ||
            str_contains($text, 'знакомлюсь') ||
            str_contains($text, 'поговори')
        ) {
            SendBotResponseAction::execute(
                $user,
                $message,
                new SimpleBotMessageNotification(
                    BearExtract::getExtract(),
                    $message
                )
            );
            return true;
        }
        return false;
    }
}

