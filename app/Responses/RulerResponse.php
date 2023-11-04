<?php

namespace App\Responses;

use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;

class RulerResponse
{
    public static function getResponse(
        User $user,
        Chat $chat,
        Reaction $reaction,
        array $message,
    ): string
    {
        $size = rand(1,40);
        $pinus = '8';
        for ($i = 1;$i<=$size;$i++) {
            $pinus .= '=';
        }
        $pinus .= 'Э';
        if ($size < 3) {
            $ranking = 'Гномик';
        }
        if ($size >= 3) {
            $ranking = 'Шалун';
        }
        if ($size >= 6) {
            $ranking = 'Затейник';
        }
        if ($size >= 10) {
            $ranking = 'Самурай';
        }
        if ($size >= 14) {
            $ranking = 'Рядовой';
        }
        if ($size >= 20) {
            $ranking = 'Лысый из бразерс';
        }
        if ($size >= 25) {
            $ranking = 'Мутант';
        }
        if ($size >= 30) {
            $ranking = 'Фантаст';
        }
        return 'У тебя '.$size.' см. Твой ранг - '.$ranking.PHP_EOL.PHP_EOL.$pinus;
    }
}
