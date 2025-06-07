<?php

namespace Tests\Unit;

use App\Actions\SetUserAction;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_and_chat_created()
    {
        $message = [
            'chat' => ['id' => 5, 'title' => 'TestChat'],
            'from' => ['id' => 10, 'first_name' => 'John', 'last_name' => 'Doe'],
        ];

        [$user, $chat] = SetUserAction::execute($message);

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertTrue($user->chats()->where('chats.chat_id', 5)->exists());
    }
}
