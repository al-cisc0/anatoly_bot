<?php

namespace Tests\Unit;

use App\Actions\SendBotResponseAction;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Mockery;
use Tests\TestCase;

class DummyNotification extends Notification
{
    public function via($notifiable)
    {
        return [];
    }
}

class SendBotResponseActionTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_notification_sent()
    {
        $user = new class extends User {
            public $notified = null;
            public function notify($notification)
            {
                $this->notified = $notification;
            }
        };

        $notification = new DummyNotification();
        $message = ['chat' => ['id' => 3]];

        SendBotResponseAction::execute($user, $message, $notification);

        $this->assertSame(3, $user->chat_id);
        $this->assertSame($notification, $user->notified);
    }
}
