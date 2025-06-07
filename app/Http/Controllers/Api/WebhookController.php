<?php

namespace App\Http\Controllers\Api;

use App\Actions\ParseBotPatternAction;
use App\Actions\ParseRatingAction;
use App\Actions\CheckIfReadOnlyAction;
use App\Actions\ApproveChatJoinRequestAction;
use App\Actions\BanTelegramUserAction;
use App\Actions\ProcessCaptchaAction;
use App\Actions\SendBotResponseAction;
use App\Actions\ParseMessageAction;
use App\Actions\SetUserAction;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
        [$this->user, $this->chat] = SetUserAction::execute($this->message);
        if ($this->message) {
            ParseMessageAction::execute($this->message, $this->user, $this->chat);
        }
        return response()->json([]);
    }

    protected function parseBotPattern(): bool
    {
        return ParseBotPatternAction::execute($this->message, $this->user);
    }

    protected function parseRating():bool
    {
        return ParseRatingAction::execute($this->message, $this->user, $this->chat);
    }

    protected function checkIfReadOnly(
        User $user,
        Chat $currentChat,
        int $rating,
    )
    {
        CheckIfReadOnlyAction::execute($user, $currentChat, $rating, $this->chat, $this->message);
    }

    protected function approveChatJoinRequest(
        int $chatId,
        int $userId,
    ): void
    {
        ApproveChatJoinRequestAction::execute($chatId, $userId);
    }

    protected function banTelegramUser(array $message)
    {
        BanTelegramUserAction::execute($message);
    }

    protected function parseMessage()
    {
        ParseMessageAction::execute($this->message, $this->user, $this->chat);
    }

    protected function processCaptcha()
    {
        ProcessCaptchaAction::execute($this->chat, $this->user, $this->message);
    }

    /**
     * Set current user who interacts with bot
     *
     */
    protected function setUser()
    {
        [$this->user, $this->chat] = SetUserAction::execute($this->message);
    }

    /**
     * Send response from bot to chat where command was executed
     *
     * @param Notification $notification
     */
    protected function sendBotResponse(Notification $notification)
    {
        SendBotResponseAction::execute($this->user, $this->message, $notification);
    }

    public function test(Request $request)
    {
        print_r($request->all(),1);
    }
}
