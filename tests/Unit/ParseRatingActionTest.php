<?php

namespace Tests\Unit;

use App\Actions\ParseRatingAction;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ParseRatingActionTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_rating_changes_on_plus_reply()
    {
        $chat = Chat::create(['chat_id' => 1, 'title' => 'chat', 'rules' => 'r']);
        $user = User::create([
            'name' => 'user',
            'telegram_id' => 1,
            'password' => bcrypt('secret'),
        ]);
        $target = User::create([
            'name' => 'target',
            'telegram_id' => 2,
            'password' => bcrypt('secret'),
        ]);

        $user->chats()->attach($chat->id, ['rating' => 0]);
        $target->chats()->attach($chat->id, ['rating' => 0]);

        $message = [
            'text' => '+',
            'reply_to_message' => ['from' => ['id' => 2]],
            'chat' => ['id' => 1],
            'message_id' => 10,
        ];

        Notification::fake();
        Mockery::mock('overload:Telegram\\Bot\\Api')
            ->shouldReceive('restrictChatMember')
            ->byDefault();

        ParseRatingAction::execute($message, $user, $chat);

        $target = $target->fresh();
        $this->assertEquals(1, $target->chats()->first()->pivot->rating);
        Notification::assertSentTo($user, \App\Notifications\SimpleBotMessageNotification::class);
    }
}
