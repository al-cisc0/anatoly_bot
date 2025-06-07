<?php

namespace Tests\Unit;

use App\Actions\ApproveChatJoinRequestAction;
use Mockery;
use Tests\TestCase;

class ApproveChatJoinRequestActionTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_calls_telegram_api()
    {
        Mockery::mock('overload:Telegram\\Bot\\Api')
            ->shouldReceive('approveChatJoinRequest')
            ->once()
            ->with([
                'chat_id' => 1,
                'user_id' => 2,
            ]);

        ApproveChatJoinRequestAction::execute(1, 2);

        $this->addToAssertionCount(1);
    }
}
