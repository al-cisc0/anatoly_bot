<?php

namespace Tests\Unit;

use App\Actions\BanTelegramUserAction;
use Mockery;
use Tests\TestCase;

class BanTelegramUserActionTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_bans_and_deletes_message()
    {
        $apiMock = Mockery::mock('overload:Telegram\\Bot\\Api');
        $apiMock->shouldReceive('banChatMember')->once()->with([
            'chat_id' => 5,
            'user_id' => 6,
            'revoke_messages' => true,
        ]);
        $apiMock->shouldReceive('deleteMessage')->once()->with([
            'chat_id' => 5,
            'message_id' => 7,
        ]);

        $message = [
            'chat' => ['id' => 5],
            'from' => ['id' => 6],
            'message_id' => 7,
        ];

        BanTelegramUserAction::execute($message);

        $this->addToAssertionCount(1);
    }
}
