<?php

namespace App\Http\Controllers\Api;

use App\Actions\AISpamDetector;
use App\CrawlerExtracts\BearExtract;
use App\CrawlerExtracts\BooksExtract;
use App\CrawlerExtracts\ChapayExtract;
use App\CrawlerExtracts\GaricExtract;
use App\CrawlerExtracts\PickupMasterExtract;
use App\Http\Controllers\Controller;
use App\Models\Beer;
use App\Models\Chat;
use App\Models\Reaction;
use App\Models\User;
use App\Notifications\SimpleBotMessageNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Api;

class WebhookController extends Controller
{

    /**
     * User who interacts with bot
     *
     * @var null
     */
    protected $user = null;

    protected $chat = null;

    /**
     * Incoming message array
     *
     * @var array
     */
    protected $message = [];

    /**
     * Handle bot webhook and decide what to do next
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function setBotInput(Request $request, string $token)
    {
//        Log::info(print_r($request->all(),1));
        if ($token != config('services.telegram-bot-api.token')) {
            return abort(403);
        }
        $this->message = $request->get('message');
        $this->setUser();
        if ($this->message) {
            $this->parseMessage();
        }
        return response()->json([]);
    }

    protected function parseBotPattern(): bool
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
        $text = mb_strtolower($this->message['text'] ?? '');
        if (
            in_array($text,$phrasesArray) ||
            str_contains(
                $text,
                'мальчик'
            ) ||
            str_contains(
                $text,
                'мальчишк'
            ) ||
            str_contains(
                $text,
                'парн'
            ) ||
            str_contains(
                $text,
                'пообща'
            ) ||
            str_contains(
                $text,
                'поболта'
            ) ||
            str_contains(
                $text,
                'познаком'
            ) ||
            str_contains(
                $text,
                'знакомлюсь'
            ) ||
            str_contains(
                $text,
                'поговори'
            )
        ) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                        BearExtract::getExtract(),
                                       $this->message
                                   ));
            return true;
        }
        return false;
    }

    protected function parseRating():bool
    {
        if (
            !empty($this->message['reply_to_message']) &&
            !empty($this->message['text']) &&
            ($this->message['text'] == '+' || $this->message['text'] == '-')
        ) {
            $request = $this->message['text'];
            $selfRating = $this->user->chats()->where('id',$this->chat->id)->first()?->pivot->rating;
            if ($selfRating < 0) {
                $this->sendBotResponse(new SimpleBotMessageNotification(
                                           'Ой кто бы тут выпендривался! У самого то рейтинг '.$selfRating,
                                       $this->message
                                       ));
                return true;
            }
            $userTgId = $this->message['reply_to_message']['from']['id'];
            if ($user = User::OfTelegramId($userTgId)->first()) {
                if ($user->id == $this->user->id) {
                    if ($request == '+') {
                        $this->sendBotResponse(new SimpleBotMessageNotification(
                                                   'Эй ты, извращенец! Аутофелляция в общественных местах - это весело, но недопустимо!',
                                                   $this->message
                                               ));
                        return true;
                    } else {
                        $this->sendBotResponse(new SimpleBotMessageNotification(
                                                   'Ну-ну, дружок, не кори себя. Все будет хорошо.',
                                                   $this->message
                                               ));
                        return true;
                    }
                }
                $currentChat = $user->chats()->where('id',$this->chat->id)->first();
                if ($currentChat) {
                    if ($request == '+') {
                        $newval = $currentChat->pivot->rating + 1;
                    } else {
                        $newval = $currentChat->pivot->rating - 1;
                    }
                    $currentChat->pivot->rating =  $newval;
                    $currentChat->pivot->save();
                } else {
                    $newval = $request == '+' ? 1 : -1;
                    $user->chats()->attach(
                        [
                            $this->chat->id => [
                                'rating' => -1
                            ]
                        ]
                    );
                }
                $this->checkIfReadOnly(
                    $user,
                    $currentChat,
                    $newval,
                );
                $this->sendBotResponse(new SimpleBotMessageNotification(
                                           'Записал твою оценку в личное дело '.$user->name.PHP_EOL.
                                           'Теперь уровень его уважения в этом чате составляет:'.PHP_EOL.PHP_EOL.$newval,
                                           $this->message
                                       ));
                return true;
            }
        }
        return false;
    }

    protected function checkIfReadOnly(
        User $user,
        Chat $currentChat,
        int $rating,
    )
    {
        if ($rating < config('bot.read_only_rating') && !$currentChat->pivot->is_readonly) {
            $telegram = new Api(config('services.telegram-bot-api.token'));
            $permissions = [
                'can_send_messages' => false,
                'can_send_media_messages' => false,
                'can_send_polls' => false,
                'can_send_other_messages' => false,
                'can_add_web_page_previews' => false,
                'can_change_info' => false,
                'can_invite_users' => false,
                'can_pin_messages' => false
            ];
            $telegram->restrictChatMember([
                                              'chat_id' => $this->chat->chat_id,
                                              'user_id' => $user->telegram_id,
                                              'permissions' => $permissions
                                          ]);
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       'Похоже, что '.$user->name.' слишком раздражает участников чата. Он получил ридонли.',
                                       $this->message
                                   ));
            $currentChat->pivot->is_readonly = 1;
            $currentChat->pivot->save();
        }
    }

    protected function approveChatJoinRequest(
        int $chatId,
        int $userId,
    ): void
    {
        try {
            $telegram = new Api(config('services.telegram-bot-api.token'));
            $telegram->approveChatJoinRequest([
                                                  'chat_id' => $chatId,
                                                  'user_id' => $userId,
                                              ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
        }
    }

    protected function banTelegramUser(array $message)
    {
        try {
            $telegram = new Api(config('services.telegram-bot-api.token'));
            $telegram->banChatMember([
                                         'chat_id'         => $message['chat']['id'],
                                         'user_id'         => $message['from']['id'],
                                         'revoke_messages' => true
                                     ]);
            $telegram->deleteMessage([
                                         'chat_id'    => $message['chat']['id'],
                                         'message_id' => $message['message_id']
                                     ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
        }
    }

    protected function parseMessage()
    {
        $joinedAt = $this->user
            ->chats()
            ->where('id',$this->chat->id)
            ->first()
            ?->pivot
            ->joined_at;

        $joinedAt = $joinedAt ? Carbon::parse($joinedAt) : null;

        $captchaPassed = $this->user
            ->chats()
            ->where('id',$this->chat->id)
            ->first()
            ?->pivot
            ->is_captcha_passed;

        if (
            !$captchaPassed &&
            $this->chat->is_captcha_enabled &&
            $this->chat->captcha_question &&
            $this->chat->captcha_answer &&
            $joinedAt &&
            $joinedAt->diffInMinutes(now()) > 5 &&
            mb_strtolower($this->message['text'] ?? '') != mb_strtolower($this->chat->captcha_answer)
        ) {
            $this->user
                ->chats()
                ->updateExistingPivot(
                    $this->chat->id,
                    [
                        'is_banned' => 1
                    ]
                );
            $this->banTelegramUser($this->message);
            return;
        } elseif (
            !$captchaPassed &&
            $this->chat->is_captcha_enabled &&
            $this->chat->captcha_question &&
            $this->chat->captcha_answer &&
            $joinedAt &&
            mb_strtolower($this->message['text'] ?? '') == mb_strtolower($this->chat->captcha_answer)
        ) {
            $this->user
                ->chats()
                ->updateExistingPivot(
                    $this->chat->id,
                    [
                        'is_captcha_passed' => 1
                    ]
                );
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       'Поздравляю! Ты успешно прошел капчу. Теперь я тебя не обижу.',
                                       $this->message
                                   ));
            $telegram = new Api(config('services.telegram-bot-api.token'));
            $telegram->deleteMessage([
                                         'chat_id'    => $this->chat->id,
                                         'message_id' => $this->message['message_id']
                                     ]);
            return;
        }


        if (Carbon::parse($this->user->created_at)->diffInMinutes(now()) < 5) {
            if ($this->parseBotPattern()) {
                return;
            }
        }
        if($this->parseRating()) {
            return;
        }
        $text = mb_strtolower($this->message['text'] ?? '');

        $messageSent = $this->user
            ->chats()
            ->where('id',$this->chat->id)
            ->first()
            ?->pivot
            ->is_message_sent;

        if (
            $this->chat->is_spam_detection_enabled &&
            !$messageSent &&
            !empty($this->message['text'])
        ) {
            $spamRating = AISpamDetector::detectSpam($text);
            if ($spamRating > $this->chat->spam_rating_limit) {
                $this->sendBotResponse(new SimpleBotMessageNotification(
                                           $this->user->name.'! Похоже, что ваше сообщение содержит спам. Ваш рейтинг спама: '.$spamRating,
                                           $this->message
                                       ));
                $this->banTelegramUser($this->message);
                $this->user
                    ->chats()
                    ->updateExistingPivot(
                        $this->chat->id,
                        [
                            'is_banned' => 1
                        ]
                    );
                return;
            } else {
                $this->sendBotResponse(new SimpleBotMessageNotification(
                                           $this->user->name.'! Ваше сообщение прошло проверку на спам. Ваш рейтинг спама: '.$spamRating,
                                           $this->message
                                       ));
            }
        }
        if (
            !$messageSent &&
            empty($this->message['new_chat_participant'])
        ) {
            $this->user
                ->chats()
                ->updateExistingPivot(
                    $this->chat->id,
                    [
                        'is_message_sent' => 1
                    ]
                );
        }
        if (
            !empty($this->message['chat_join_request']) &&
            $this->chat->is_join_request_approve_enabled
        ) {
            $chatJoinRequest = $this->message['chat_join_request'];

            $chatId = $chatJoinRequest['chat']['id'];
            $userId = $chatJoinRequest['from']['id'];
            $this->approveChatJoinRequest($chatId, $userId);
        }
        $reactions = Reaction::whereNull('chat_id')
                              ->orWhere('chat_id', $this->chat->id)
                              ->get();
        foreach ($reactions as $reaction) {
            $keyPhrase = $reaction->key_phrase;
            if ($reaction->is_array) {
                $keyPhrase = explode(
                    '|||',
                    $keyPhrase
                );
            } else {
                $keyPhrase = [$keyPhrase];
            }
            foreach ($keyPhrase as $phrase) {
                if (str_contains(
                    $text,
                    $phrase
                )) {
                    if ($reaction->is_class_trigger && (!$reaction->is_daily_updated || Carbon::parse($reaction->updated_at)->diffInDays(now()) > 0 || empty($reaction->response['message']))) {
                        $class = 'App\Responses\\'.$reaction->response['class'];
                        $response = $class::getResponse(
                            $this->user,
                            $this->chat,
                            $reaction,
                            $this->message
                        );
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
                    $this->sendBotResponse(new SimpleBotMessageNotification(
                                               $response,
                                               $this->message
                                           ));
                    break;
                }
            }
        }

        if (!empty($this->message['new_chat_participant']['is_bot']) && $this->message['new_chat_participant']['is_bot'] == 1) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                'Эй ты, ублюдок электрический, ну-ка укажи все светофоры!',
                $this->message
                                   ));
        }
        if (!empty($this->message['new_chat_participant']) &&
            (
                empty($this->message['new_chat_participant']['is_bot']) ||
                $this->message['new_chat_participant']['is_bot'] == 0)
        ) {
            if ($this->chat?->rules) {
                $text = 'почитай правила чата: '.PHP_EOL.PHP_EOL.$this->chat->rules;
            } else {
                $text = 'расскажи куда путь держишь.';
            }
            $this->sendBotResponse(new SimpleBotMessageNotification(
                'Приветствую тебя, путник. Присаживайся, отдохни, выпей чаю и '.$text,
                $this->message
                                   ));
            if (
                $this->chat->is_captcha_enabled &&
                $this->chat->captcha_question &&
                $this->chat->captcha_answer
            ) {
                $this->processCaptcha();
            }

        }
    }

    protected function processCaptcha()
    {
        $this->sendBotResponse(new SimpleBotMessageNotification(
            $this->chat->captcha_question . ' На ответ у тебя есть 5 минут. Время пошло.',
            $this->message
        ));
    }

    /**
     * Set current user who interacts with bot
     *
     */
    protected function setUser()
    {
        if (!empty($this->message['chat']['id'])) {
            $this->chat = Chat::firstOrCreate(
                [
                    'chat_id' => $this->message['chat']['id']
                ],
                [
                    'title' => $this->message['chat']['title'] ?? 'user',
                    'rules' => '',
                ]
            );
        }
        if (!empty($this->message['from']['id'])) {
            $telegramId = $this->message['from']['id'];
            $userName = '';
            if (!empty($this->message['from']['first_name'])) {
                $userName .= $this->message['from']['first_name'];
            }
            if (!empty($this->message['from']['last_name'])) {
                $userName .= ' ' . $this->message['from']['last_name'];
            }
            $this->user = User::ofTelegramId($telegramId)
                              ->first();
            $isOwner = 0;
            $isActive = 0;
            if (config('bot.owner_id') == $telegramId) {
                $isOwner = 1;
                $isActive = 1;
            }
            if (!$this->user) {
                $this->user = User::create([
                                               'telegram_id' => $telegramId,
                                               'name'        => $userName,
                                               'is_active'   => $isActive,
                                               'is_admin'    => $isOwner,
                                               'password'    => Hash::make(Str::random(8))
                                           ]);
            }
            if (!$this->user->chats()->where('id',$this->chat->id)->exists()) {
                $this->user->chats()->attach([$this->chat->id => ['joined_at' => now()]]);
            }
        }
    }

    /**
     * Send response from bot to chat where command was executed
     *
     * @param Notification $notification
     */
    protected function sendBotResponse(Notification $notification)
    {
        try {
            $this->user->chat_id = $this->message['chat']['id'];
            $this->user->notify($notification);
        } catch (\Exception $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
        }
    }

    public function test(Request $request)
    {
        print_r($request->all(),1);
    }
}
