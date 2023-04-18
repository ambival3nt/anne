<?php

namespace App\Core;

use App\Core\BioGPT\BioGPTCore;
use App\Core\config\BotCredentials;
use App\Core\config\CommonKnowledge;
use App\Core\config\InitBotConfig;
use App\Core\OpenAI\OpenAICore;
use App\Models\Anne;
use Discord\Discord;
use Discord\Http\Drivers\React;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use App\Core\commands\HandleCommandProcess;
use App\Core\commands\HelpCommand;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class bot_main
{
    public function init()
    {

        //initialize the bot


        $selfInfo = Anne::all()->first() ?? null;

        if(!$selfInfo){
            $selfInfo = Anne::firstOrCreate(['id' => 1, 'last_message' => '-', 'last_user' => '-', 'last_response' => '-', 'earmuffs' => 0, 'debug' => 0]);
        }

        $ownerId = getenv('OWNER_ID');


        $commandTag = (new InitBotConfig)->commandTag();
        $token = (new BotCredentials)->getToken();


        // start discord bot loop
        $discord = new Discord(['token' => $token,
            'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT]);

        $discord->on('ready', function (Discord $discord) use ($commandTag, $selfInfo, $ownerId) {

            $discord->on('message', function (Message $message, Discord $discord) use ($commandTag, $selfInfo, $ownerId) {

                $reply = null;
                $mention = null;
                //check for mention
                if(count($message->mentions) > 0) {
                    if ($message->mentions->first()->id === $discord->id && !$message->author->bot) {
                        $mention = $message->mentions->first() ?? null;
                    }
                }

                if($message->referenced_message!==null){
                    if ($message->referenced_message === $discord->id && !$message->author->bot) {
                        $reply = $message->referenced_message ?? null;
                    }
                }


                //if earmuffs are on, bot will only respond to owner
                if($selfInfo->earmuffs===0 || ($message->author->id === $ownerId && $selfInfo->earmuffs===1)) {

                    //if it's talking to anne, or if it's mentioned, or if it's a test command
                    try {
                        if (str_starts_with(strtolower($message->content), 'anne')
                            || $mention
                            || str_starts_with(strtolower($message->content), '-=test')
                            || str_starts_with(strtolower($message->content), '-=think')
                        ) {

                            $discord->getChannel($message->channel_id)->broadcastTyping()
                                ->then(function () use ($message, $discord, $mention) {
                                $anne = new OpenAICore();
                                $anne->query($message, $discord, $mention);

                            })->done();






                        }


                        //BioGPT query (gils this took a lot of work lol)
                        if(stripos($message->content, 'dr. anne')!==false
                            && !$message->author->bot){

//                          $anne = new BioGPTCore();

                            //  $bioGPTQuery = $anne->query($message->content);


                            return $message->reply('You have Lupus.');
//                           return $message->reply($bioGPTQuery);


                        }

                        //command tag path
                        if (str_starts_with($message->content, $commandTag['tag']) && !$message->author->bot) {


                            $contentData = "";
                            $commandHasContent = stripos($message->content, ' ') && " ";
                            $command = substr($message->content, $commandTag['tagLength']);
                            $commandArray = explode(' ', $command);

                            if (HandleCommandProcess::isValidCommand($commandArray[0])) {

                                HandleCommandProcess::runCommandOnContent($command, $contentData, $message, $message->user_id === $ownerId, $commandArray);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::debug($e->getMessage());
                        return $message->reply('Stoppit.');
                    }
                }
            });

        });

        $discord->run();
    }
}
