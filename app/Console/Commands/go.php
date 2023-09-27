<?php

namespace App\Console\Commands;

use App\Models\TriviaGame;
use App\Models\TriviaPlayers;
use Illuminate\Console\Command;
//use React\ChildProcess\Process;
use Illuminate\Support\Facades\Log;
use App\Core\bot_main;




class go extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disdain:go';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the bot.';


    /**
     * @throws \Exception
     */
    public function handle()
    {
        $game = TriviaGame::where('id', '>', 0)->first() ?? null;
        if (data_get($game, 'id', null) !== null) {
            TriviaGame::destroy($game->id);
        }
        $players = TriviaPlayers::where('user_id', '>', 0)->first() ?? null;
        if (data_get($players, 'user_id', null) !== null) {
            TriviaPlayers::destroy($players->user_id);
        }

        $bot = new bot_main();

        Log::channel('db')->debug("Bot has started.");
        echo("\nBot is running.");
        $bot->init();
    }

}
