<?php

namespace Tests\Unit;

use App\Actions\ParseBotPatternAction;
use App\CrawlerExtracts\BearExtract;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ParseBotPatternActionTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_detects_pattern_and_sends_response()
    {
        $user = User::create([
            'name' => 'test',
            'telegram_id' => 1,
            'password' => bcrypt('secret'),
        ]);
        $message = [
            'text' => 'кто тут кто тут?',
            'chat' => ['id' => 1],
            'message_id' => 42,
        ];

        Mockery::mock('alias:' . BearExtract::class)
            ->shouldReceive('getExtract')
            ->andReturn('bear');

        Notification::fake();

        $this->assertTrue(ParseBotPatternAction::execute($message, $user));
        Notification::assertSentTo($user, \App\Notifications\SimpleBotMessageNotification::class);
    }
}
