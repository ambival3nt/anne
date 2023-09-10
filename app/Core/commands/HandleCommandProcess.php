<?php

namespace App\Core\commands;


use App\Core\Features\Lichess;
use App\Core\OpenAI\OpenAICore;
use App\Core\OpenAI\Prompts\analyzeUserInput;
use App\Core\Spotify\GetAPIToken;
use App\Core\Spotify\QueryAPI;
use App\Core\Trivia\TriviaCore;
use App\Core\VectorDB\VectorQueryReturn;
use App\Core\YouTube\VideoQuery;
use App\Enums\AnneActions;
use App\Models\Anne;
use App\Models\Playlist;
use App\Models\TriviaPlayers;
use App\Services\ButtonService;
use Carbon\Carbon;
use Discord\Builders\MessageBuilder;
use Illuminate\Support\Facades\Log;
use phpw2v\Word2Vec;

class HandleCommandProcess
{

    public static function isValidCommand($command)
    {
        Log::channel('db')->debug("command in: " . $command);
        $commandList = [
            'ping',
            'help',
            'earmuffs',
            'debug',
            'embed',
            'chess',
            'spam',
            'command',
            'playlist',
            'fart',
            'test',
            'think',
            'yoot',
            'trivia',
        ];

        return in_array($command, $commandList, true);
    }

    public static function runCommandOnContent($command, $content, $message, $owner, $commandArray, $discord)
    {
        Log::channel('daily')->debug("Command: $command\nContent: $content\nMessage: $message\nOwner: $owner");

        $arg = "";

        foreach ($commandArray as $index => $commandPart) {
            if ($index === 0) {
                $command = $commandPart;
            } else {
                $arg = $commandPart;
            }
        }
Log::channel('db')->debug("Arg: $arg");


        switch ($command) {


            case 'yoot':

                $m = substr($message->content, 6);
                $youtubeResponse = (new VideoQuery)->search($m);
                return $message->reply($youtubeResponse);

            case 'test':
                $vectorReturn = new VectorQueryReturn(new OpenAICore());
                return $vectorReturn->vectorQueryReturnTest($message);


        // Emotional analysis test route
            case 'think':
                try {
                    $m = substr($message->content, 7);

                    //Analyze the user's message for abstracts
                    return $message->reply((new analyzeUserInput())->basic($m, $message->author->displayname));

                } catch (\Exception $e) {
                    return $message->reply('NOP sorry, something went wrong: ' . $e->getMessage());
                }

//Dumps the whole message json
            case 'spam':

                $encodedArray = str_split(json_encode($message, 128), 2000);

                foreach ($encodedArray as $item) {
                    $message->reply($item);
                }
                return $message->reply("Spamming complete.");


            //ping command
            case 'ping':
                return $message->reply("Oh I'll ping you, pal.");

            //help command
            case 'help':
                $activeCommand = (new HelpCommand)->index($message, $content);
                return $activeCommand->index($message, $content);

            //earmuffs (respond to owner only)
            case 'earmuffs':
                if ($owner) {
                    if ($arg === 'on') {
                        $anne = Anne::all()->first()->earmuffsToggle(true);
                        $anne->save();
                        Log::channel('db')->debug("on - " . json_encode($anne, 128));
                        return $message->reply("Earmuffs are on.");
                    } elseif ($arg === 'off') {
                        $anne = Anne::all()->first()->earmuffsToggle(false);
                        $anne->save();
                        Log::channel('db')->debug("off - " . json_encode($anne, 128));
                        return $message->reply("Earmuffs are off.");
                    } else {
                        return $message->reply("Earmuffs are currently " . ((boolean)Anne::all()->first()->earmuffs ? 'on' : 'off'));
                    }
                } else {
                    return $message->reply("No you're not even my real dad you put on the earmuffs.");
                }
                break;

            //debug toggle
            case 'debug':
                if ($owner) {
                    if ($arg === 'on') {
                        $anne = Anne::all()->first()->debugToggle(true);
                        $anne->save();
                        Log::channel('db')->debug("on - " . json_encode($anne, 128));
                        return $message->reply("Debug mode is on.");
                    } elseif ($arg === 'off') {
                        $anne = Anne::all()->first()->debugToggle(false);
                        $anne->save();
                        Log::channel('db')->debug("off - " . json_encode($anne, 128));
                        return $message->reply("Debug mode is off.");
                    } else {
                        return $message->reply("Debug mode is currently " . ((boolean)Anne::all()->first()->debug ? 'on' : 'off'));
                    }
                } else {
                    return $message->reply("How about instead of debug me we many bees you?");
                }
                break;
            case 'embed':
                $time1 = Carbon::now();
                $vecThing = new Word2Vec();
                $embed = $vecThing->wordVec($arg);
                $time2 = Carbon::now();
                $timeDiff = $time1->diffInMilliseconds($time2);
                $output =
                    "Input: $arg\n
                    Output: " . json_encode($embed) . "\n
                    Took: " . $timeDiff . "ms";
                return $message->reply($output);
                break;

            //lichess command
            //TODO: have anne just catch this
            case 'chess':
                $lichess = new Lichess;
                try {
                    if (stripos($arg, 'lichess') !== false) {
                        $lichessOutput = $lichess->exportGame(Lichess::getLichessGameId($arg));
                    }
                    return $message->reply($lichessOutput);
                } catch (\Exception $e) {
                    return $message->reply("Invalid lichess link.");
                }
                break;

            case 'command':
                return self::extractSelfCommand($commandArray, $message);
                break;

            //test command for playlist functionality
            case 'playlist':
                $default = true;

                if($message->mentions->count() > 0){
                   Log::channel('db')->debug('false');
                    $default=false;
                }

                if($arg == 'top') {
                    Log::channel('db')->debug('top list');
                    return Playlist::controller($discord, $message, $arg);

                } else {
                    Log::channel('db')->debug('regular');
                    return self::createPlaylistMessage($discord, $message, 1, $default);
                }
                break;
//                return $message->channel->sendMessage("That's all the music posted today.");

            case 'trivia':

                if($arg=='top'){
                    $trivia = new TriviaPlayers;
                    $topPlayers = $trivia->getTopPlayers();
                    $topPlayersOutput = "Top players:\n";
                    foreach($topPlayers as $player){
                        $topPlayersOutput .= $player->username . " - " . $player->score . "\n";
                    }
                    return $message->channel->sendMessage($topPlayersOutput);
                }

               $game = new TriviaCore;
               $gameStatus = $game->initGame($discord, $message);

                   if ($gameStatus['error']) {
                       return $message->reply($gameStatus['message']);
               }elseif($gameStatus['game']){
                   $questionOutput =
                       "Round " . $gameStatus['game']->round . "\n
                        Question: " . $gameStatus['game']->question . "\n
                        ";
                   return $message->channel->sendMessage($questionOutput);

               }


//                $out = $trivia->init($message);
//                for($i=0; $i<strlen($out);$i+=1999) {
//                    $message->channel->sendMessage(substr($out,$i,1999));
//                }
//                return $message->channel->sendMesssage('k');
            break;

            case 'fart':

                $json = json_encode($user = $message->author, 128);
                for($i=0; $i<strlen($json);$i+=1999){
                    $message->reply(substr($json,$i,1999));
                }
               return $message->reply("Farted.");



            default:
                return $message->reply("Uh... sure.");
        }

    }

    /**
     * This function searches the message for a potential command/action to be executed by the bot.
     * @param $commandArray
     * @param $message
     * @return mixed
     */
    protected static function extractSelfCommand($commandArray, $message): mixed
    {

        // this is an array  that contains the elements of the user input word by word
        $commandInputArray = array_shift($commandArray);
        $commandInputArray = implode(" ", $commandArray);

        //analyze the message
        $command = new analyzeUserInput();
        //commandList is a string list of commands that the bot has decided are appropriate to use
        $commandList = $command->actions($commandInputArray, $message->author->username);

        $anneActionList = AnneActions::list();

        foreach($anneActionList as $action){
            if(stripos($commandList, $action)){
               $message->channel->sendMessage('```I have the urge to '.$action.'```');
            }
        }

        return $message->reply($commandList);
    }

    /**
     * @param $discord
     * @param $message
     * @return mixed
     */
    public static function createPlaylistMessage($discord, $message, $currentPage=1, $default=true): mixed
    {
        $playlist = new Playlist;
        if($default) {
            $retrievedList = $playlist->getListForToday($discord);
            $typeOfList = "today";
            $listTitle = Carbon::today()->toFormattedDayDateString();
        } else {
            $retrievedList = $playlist->getPlaylistForUser($message->mentions->first()->id, $discord);
            $typeOfList = "user";
            $listTitle = $message->mentions->first()->username . " - Total: " . count($retrievedList['embeds']);
        }
        $playlistPageArray = [];

        //get total, then get number of pages
        $totalItems = count($retrievedList['embeds']);
        $totalPages = ceil($totalItems / 5);

        //if there wasn't a playlist...
        if (!$retrievedList['hasItems']) {
            return $message->channel->sendMessage("No items in playlist for today. How boring.");
        } else {

            //create array of pages (one page is one message) of 5 embeds max each
            $playlistMessage = new MessageBuilder();

            $page = 1;
            $i=1;

            //this loops the full list of returned songs and puts them in an array in groups of 5, index is page number
            foreach($retrievedList['embeds'] as $embed) {

                $playlistMessage->addEmbed($embed);

                if ($i % 5 === 0 && $i !== 0) {
                    $playlistPageArray[$page] = $playlistMessage;
                    $playlistMessage = new MessageBuilder();
                    $page++;
                }
                $i++;
            }
            //this guy handles the last page, which may not be a full page
            $playlistPageArray[$page] = $playlistMessage;
        }

        //if there is no array built, assume it is one small page
        if (!$playlistPageArray) {
            $playlistPageArray[$currentPage] = $playlistMessage;
        }


        //this is a function that is going to run every time a button inside this function is clicked. do not panic
        $messageWithPaginator = ButtonService::buildPaginator($totalPages, $currentPage, $discord, $playlistPageArray);

        //output
        $message->channel->sendMessage("Playlist for: " . $listTitle);
        return $message->channel->sendMessage($messageWithPaginator);

    }

}
