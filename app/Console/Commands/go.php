<?php

namespace App\Console\Commands;

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

        $bot = new bot_main();

        Log::debug("Bot has started.");
        echo("\nBot is running.");
        $bot->init();
    }

}
