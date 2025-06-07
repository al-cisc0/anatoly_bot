<?php

namespace Tests\Unit;

use App\Actions\ApproveChatJoinRequestAction;
use App\Actions\ParseMessageAction;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ParseMessageActionTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_join_request_is_approved_when_enabled()
    {
        $user = User::create([
            'name' => 'test',
            'telegram_id' => 1,
            'password' => bcrypt('secret'),
        ]);
        $chat = Chat::create([
            'chat_id' => 10,
            'title' => 'chat',
            'rules' => 'r',
            'is_join_request_approve_enabled' => true,
        ]);

        $message = [
            'chat_join_request' => [
                'chat' => ['id' => 10],
                'from' => ['id' => 50],
            ],
        ];

        Mockery::mock('overload:Telegram\\Bot\\Api')
            ->shouldReceive('approveChatJoinRequest')
            ->once()
            ->with([
                'chat_id' => 10,
                'user_id' => 50,
            ]);

        ParseMessageAction::execute($message, $user, $chat);

        $this->addToAssertionCount(1);
    }
}
