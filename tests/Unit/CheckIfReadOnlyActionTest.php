<?php

namespace Tests\Unit;

use App\Actions\CheckIfReadOnlyAction;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class CheckIfReadOnlyActionTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_user_gets_restricted_when_rating_low()
    {
        config(['bot.read_only_rating' => -10]);

        $user = User::create([
            'name' => 'test',
            'telegram_id' => 10,
            'password' => bcrypt('secret'),
        ]);
        $chat = Chat::create(['chat_id' => 1, 'title' => 'chat', 'rules' => 'r']);
        $user->chats()->attach($chat->id, ['rating' => -11, 'is_readonly' => 0]);
        $currentChat = $user->chats()->where('id', $chat->id)->first();

        Mockery::mock('overload:Telegram\\Bot\\Api')
            ->shouldReceive('restrictChatMember')
            ->once();

        Notification::fake();

        CheckIfReadOnlyAction::execute($user, $currentChat, -11, $chat, ['chat'=>['id'=>1]]);

        $user->refresh();
        $this->assertTrue($user->chats()->first()->pivot->is_readonly == 1);
        Notification::assertSentTo($user, \App\Notifications\SimpleBotMessageNotification::class);
    }
}
