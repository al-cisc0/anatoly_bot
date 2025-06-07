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

    public function test(Request $request)
    {
        print_r($request->all(),1);
    }
}
