<?php

namespace App\Core;

use App\Core\commands\HandleCommandProcess;
use App\Core\config\BotCredentials;
use App\Core\config\InitBotConfig;
use App\Core\Features\Playlist;
use App\Core\OpenAI\OpenAICore;
use App\Core\Trivia\TriviaCore;
use App\Models\Anne;
use App\Models\TriviaGame;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Illuminate\Support\Facades\Log;
use React\EventLoop\Loop;

class bot_main
{

    public function __construct(Discord $discord = null)
    {
        $this->discord = $discord ?? null;
    }

    public function init()
    {


        //initialize the bot

        $lastMessage = null;

        $selfInfo = Anne::all()->first() ?? null;

        if(!$selfInfo){
            $selfInfo = Anne::firstOrCreate(['id' => 1, 'last_message' => '-', 'last_user' => '-', 'last_response' => '-', 'earmuffs' => 0, 'debug' => 0]);
        }

        $ownerId = getenv('OWNER_ID');


        $commandTag = (new InitBotConfig)->commandTag();
        $token = (new BotCredentials)->getToken();



        if($this->discord){
            $discord = $this->discord;
        }else{
            // start discord bot loop
            $discord = new Discord([
                'token' => $token,
                'intents' => Intents::getAllIntents(),
                'loop' => Loop::get(),
            ]);


        }


        $discord->on('ready', function (Discord $discord) use ($commandTag, $selfInfo, $ownerId, $lastMessage) {

            $discord->on('message', function (Message $message, Discord $discord) use ($commandTag, $selfInfo, $ownerId, $lastMessage) {

                //checks for music link for playlist feature (why did i do this)
               if(!$message->author->bot) {
                   $this->isMusicLink($message);
               }

               $triviaGame = TriviaGame::first() ?? null;
                if($triviaGame && $triviaGame->count() > 0){
                   $triviaCore = new TriviaCore();
                   $triviaReturn = $triviaCore->gameHandler($discord, $triviaGame, $message);

                   if($triviaReturn['message']){
                       $message->channel->sendMessage($triviaReturn['message']);
                   }
                   if($triviaReturn['error']){
                       $triviaGame->abort();
                       $message->channel->sendMessage('Error occurred. Aborting Game.');
                   }
                }

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
                            || str_starts_with(strtolower($message->content), '-=spam')

                        ) {
                            $anne = new OpenAICore();
                            $anne->query($message, $discord, $mention, null,$lastMessage,$discord);

                        }
                    $lastMessage = $message;

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

                                HandleCommandProcess::runCommandOnContent($command, $contentData, $message, $message->user_id === $ownerId, $commandArray, $discord);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::channel('db')->debug($e->getMessage().' on line '. $e->getLine() . ' in ' . $e->getFile());
                        return $message->reply("Stoppit.");
//                        return $message->reply("Stoppit. I'll just tell you what's wrong:\n" . $e->getMessage().' on line '. $e->getLine() . ' in ' . $e->getFile());
                    }
                }
            });

        });

        $discord->run();
    }

    /**
     * @param Message $message
     * @return void
     */
    function isMusicLink(Message $message): void
    {
        if (str_contains($message->content, 'https://') || str_contains($message->content, 'http://')) {
            if (stripos($message->content, 'youtube.com') !== false
                || stripos($message->content, 'youtu.be') !== false
                || stripos($message->content, 'soundcloud.com') !== false
                || stripos($message->content, 'spotify.com') !== false
                || stripos($message->content, 'open.spotify.com') !== false
            ) {
                Log::channel('db')->debug('isMusicLink - ' . $message->content);
                Playlist::grabMusicLinkUrl($message);
            }
        }
    }

}
