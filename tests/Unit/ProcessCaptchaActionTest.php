<?php

namespace Tests\Unit;

use App\Actions\ProcessCaptchaAction;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class ProcessCaptchaActionTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_captcha_question_sent()
    {
        $chat = Chat::create([
            'chat_id' => 1,
            'title' => 'chat',
            'rules' => 'r',
            'captcha_question' => 'q',
            'captcha_answer' => 'a',
        ]);
        $user = User::create([
            'name' => 'test',
            'telegram_id' => 1,
            'password' => bcrypt('secret'),
        ]);
        $message = ['chat' => ['id' => 1]];

        Notification::fake();

        ProcessCaptchaAction::execute($chat, $user, $message);

        Notification::assertSentTo($user, \App\Notifications\SimpleBotMessageNotification::class);
    }
}
