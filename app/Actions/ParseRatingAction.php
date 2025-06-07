<?php

namespace App\Actions;

use App\Models\Chat;
use App\Models\User;
use App\Notifications\SimpleBotMessageNotification;

class ParseRatingAction
{
    public static function execute(array $message, User $user, Chat $chat): bool
    {
        if (!empty($message['reply_to_message']) && !empty($message['text']) && ($message['text'] == '+' || $message['text'] == '-')) {
            $request = $message['text'];
            $selfRating = $user->chats()->where('id', $chat->id)->first()?->pivot->rating;
            if ($selfRating < 0) {
                SendBotResponseAction::execute(
                    $user,
                    $message,
                    new SimpleBotMessageNotification(
                        'Ой кто бы тут выпендривался! У самого то рейтинг ' . $selfRating,
                        $message
                    )
                );
                return true;
            }
            $userTgId = $message['reply_to_message']['from']['id'];
            if ($targetUser = User::OfTelegramId($userTgId)->first()) {
                if ($targetUser->id == $user->id) {
                    if ($request == '+') {
                        SendBotResponseAction::execute(
                            $user,
                            $message,
                            new SimpleBotMessageNotification(
                                'Эй ты, извращенец! Аутофелляция в общественных местах - это весело, но недопустимо!',
                                $message
                            )
                        );
                        return true;
                    }
                    SendBotResponseAction::execute(
                        $user,
                        $message,
                        new SimpleBotMessageNotification(
                            'Ну-ну, дружок, не кори себя. Все будет хорошо.',
                            $message
                        )
                    );
                    return true;
                }
                $currentChat = $targetUser->chats()->where('id', $chat->id)->first();
                if ($currentChat) {
                    $newval = $request == '+' ? $currentChat->pivot->rating + 1 : $currentChat->pivot->rating - 1;
                    $currentChat->pivot->rating = $newval;
                    $currentChat->pivot->save();
                } else {
                    $newval = $request == '+' ? 1 : -1;
                    $targetUser->chats()->attach([
                        $chat->id => [
                            'rating' => -1
                        ]
                    ]);
                }
                CheckIfReadOnlyAction::execute($targetUser, $currentChat, $newval, $chat, $message);
                SendBotResponseAction::execute(
                    $user,
                    $message,
                    new SimpleBotMessageNotification(
                        'Записал твою оценку в личное дело ' . $targetUser->name . PHP_EOL . 'Теперь уровень его уважения в этом чате составляет:' . PHP_EOL . PHP_EOL . $newval,
                        $message
                    )
                );
                return true;
            }
        }
        return false;
    }
}

