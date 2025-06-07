<?php

namespace App\Actions;

use App\Actions\ParseBotPatternAction;
use App\Actions\ParseRatingAction;
use App\Actions\ApproveChatJoinRequestAction;
use App\Actions\BanTelegramUserAction;
use App\Actions\ProcessCaptchaAction;
use App\Actions\SendBotResponseAction;
use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;
use App\Notifications\SimpleBotMessageNotification;
use Carbon\Carbon;
use App\Actions\AISpamDetector;

class ParseMessageAction
{
    public static function execute(array $message, User $user, Chat $chat): void
    {
        $joinedAt = $user->chats()->where('id', $chat->id)->first()?->pivot->joined_at;
        $joinedAt = $joinedAt ? Carbon::parse($joinedAt) : null;

        $captchaPassed = $user->chats()->where('id', $chat->id)->first()?->pivot->is_captcha_passed;

        if (
            !$captchaPassed &&
            $chat->is_captcha_enabled &&
            $chat->captcha_question &&
            $chat->captcha_answer &&
            $joinedAt &&
            $joinedAt->diffInMinutes(now()) > 5 &&
            mb_strtolower($message['text'] ?? '') != mb_strtolower($chat->captcha_answer)
        ) {
            $user->chats()->updateExistingPivot($chat->id, ['is_banned' => 1]);
            BanTelegramUserAction::execute($message);
            return;
        } elseif (
            !$captchaPassed &&
            $chat->is_captcha_enabled &&
            $chat->captcha_question &&
            $chat->captcha_answer &&
            $joinedAt &&
            mb_strtolower($message['text'] ?? '') == mb_strtolower($chat->captcha_answer)
        ) {
            $user->chats()->updateExistingPivot($chat->id, ['is_captcha_passed' => 1]);
            SendBotResponseAction::execute(
                $user,
                $message,
                new SimpleBotMessageNotification(
                    'Поздравляю! Ты успешно прошел капчу. Теперь я тебя не обижу.',
                    $message
                )
            );
            $telegram = new \Telegram\Bot\Api(config('services.telegram-bot-api.token'));
            $telegram->deleteMessage([
                'chat_id'    => $chat->id,
                'message_id' => $message['message_id']
            ]);
            return;
        }

        if (Carbon::parse($user->created_at)->diffInMinutes(now()) < 5) {
            if (ParseBotPatternAction::execute($message, $user)) {
                return;
            }
        }

        if (ParseRatingAction::execute($message, $user, $chat)) {
            return;
        }

        $text = mb_strtolower($message['text'] ?? '');

        $messageSent = $user->chats()->where('id', $chat->id)->first()?->pivot->is_message_sent;

        if ($chat->is_spam_detection_enabled && !$messageSent && !empty($message['text'])) {
            $spamRating = AISpamDetector::detectSpam($text);
            if ($spamRating > $chat->spam_rating_limit) {
                SendBotResponseAction::execute(
                    $user,
                    $message,
                    new SimpleBotMessageNotification(
                        $user->name . '! Похоже, что ваше сообщение содержит спам. Ваш рейтинг спама: ' . $spamRating,
                        $message
                    )
                );
                BanTelegramUserAction::execute($message);
                $user->chats()->updateExistingPivot($chat->id, ['is_banned' => 1]);
                return;
            }
            SendBotResponseAction::execute(
                $user,
                $message,
                new SimpleBotMessageNotification(
                    $user->name . '! Ваше сообщение прошло проверку на спам. Ваш рейтинг спама: ' . $spamRating,
                    $message
                )
            );
        }

        if (!$messageSent && empty($message['new_chat_participant'])) {
            $user->chats()->updateExistingPivot($chat->id, ['is_message_sent' => 1]);
        }

        if (!empty($message['chat_join_request']) && $chat->is_join_request_approve_enabled) {
            $chatJoinRequest = $message['chat_join_request'];
            $chatId = $chatJoinRequest['chat']['id'];
            $userId = $chatJoinRequest['from']['id'];
            ApproveChatJoinRequestAction::execute($chatId, $userId);
        }

        $reactions = Reaction::whereNull('chat_id')
            ->orWhere('chat_id', $chat->id)
            ->get();
        foreach ($reactions as $reaction) {
            $keyPhrase = $reaction->key_phrase;
            if ($reaction->is_array) {
                $keyPhrase = explode('|||', $keyPhrase);
            } else {
                $keyPhrase = [$keyPhrase];
            }
            foreach ($keyPhrase as $phrase) {
                if (str_contains($text, $phrase)) {
                    if ($reaction->is_class_trigger && (!$reaction->is_daily_updated || Carbon::parse($reaction->updated_at)->diffInDays(now()) > 0 || empty($reaction->response['message']))) {
                        $class = 'App\\Responses\\' . $reaction->response['class'];
                        $response = $class::getResponse($user, $chat, $reaction, $message);
                        if ($reaction->is_daily_updated) {
                            $reaction->response = [
                                'class' => $reaction->response['class'],
                                'message' => $response
                            ];
                            $reaction->save();
                        }
                    } else {
                        $response = $reaction->response['message'];
                    }
                    SendBotResponseAction::execute(
                        $user,
                        $message,
                        new SimpleBotMessageNotification($response, $message)
                    );
                    break;
                }
            }
        }

        if (!empty($message['new_chat_participant']['is_bot']) && $message['new_chat_participant']['is_bot'] == 1) {
            SendBotResponseAction::execute(
                $user,
                $message,
                new SimpleBotMessageNotification(
                    'Эй ты, ублюдок электрический, ну-ка укажи все светофоры!',
                    $message
                )
            );
        }

        if (!empty($message['new_chat_participant']) && (empty($message['new_chat_participant']['is_bot']) || $message['new_chat_participant']['is_bot'] == 0)) {
            if ($chat?->rules) {
                $textCaptcha = 'почитай правила чата: ' . PHP_EOL . PHP_EOL . $chat->rules;
            } else {
                $textCaptcha = 'расскажи куда путь держишь.';
            }
            SendBotResponseAction::execute(
                $user,
                $message,
                new SimpleBotMessageNotification(
                    'Приветствую тебя, путник. Присаживайся, отдохни, выпей чаю и ' . $textCaptcha,
                    $message
                )
            );
            if ($chat->is_captcha_enabled && $chat->captcha_question && $chat->captcha_answer) {
                ProcessCaptchaAction::execute($chat, $user, $message);
            }
        }
    }
}

