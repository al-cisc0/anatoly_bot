<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test {--promt=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For testing random things';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = \OpenAI::client(config('bot.openapi_token'));
        $response = $client->chat()->create([
                                                'model' => 'gpt-4o',
                                                'messages' => [
                                                    ['role' => 'user', 'content' => $this->option('promt')],
                                                ],
                                            ]);
        try {
            $message = $response->choices[0]->message->content;
        } catch (\Exception $e) {
            $message = 'Ась?';
        }
        print_r($message);
        return Command::SUCCESS;
    }
}
