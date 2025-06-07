<?php

namespace Tests\Unit;

use App\Actions\AISpamDetector;
use Mockery;
use PHPUnit\Framework\TestCase;

class AISpamDetectorTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_detect_spam_returns_integer()
    {
        $this->markTestSkipped('Unable to mock OpenAI static client in this environment.');

        $chatMock = new class {
            public function create(array $params)
            {
                return (object)[
                    'choices' => [
                        (object)['message' => (object)['content' => '5']]
                    ]
                ];
            }
        };

        $clientMock = new class($chatMock) {
            public function __construct(private $chat) {}
            public function chat() { return $this->chat; }
        };

        Mockery::mock('alias:OpenAI')
            ->shouldReceive('client')
            ->andReturn($clientMock);

        $this->assertSame(5, AISpamDetector::detectSpam('test'));
    }
}
