<?php

namespace App\Core\commands;


use App\Core\Features\Lichess;
use App\Core\Google\Search;
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
use App\Models\TriviaScores;
use App\Services\ButtonService;
use Carbon\Carbon;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Guild\Emoji;
use Discord\Parts\Guild\Guild;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenAI;
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
            'compare',
            'database',
            ];

        return in_array($command, $commandList, true);
    }

    public static function runCommandOnContent($command, $content, $message, $owner, $commandArray, $discord)
    {
        $client=OpenAI::client(getenv('OPENAI_API_KEY'));

//        Log::debug("Command: $command\nContent: $content\nMessage: $message\nOwner: $owner");

        $commandArg = "";

        foreach ($commandArray as $index => $commandPart) {
            if ($index === 0) {
                $command = $commandPart;
            } else {
                $commandArg .= " " . $commandPart;
            }
            $commandArg = trim($commandArg);
        }
//Log::channel('db')->debug("Arg: $commandArg");



        switch ($command) {

            case 'yoot':

                $m = substr($message->content, 6);
                $youtubeResponse = (new VideoQuery)->search($m);
                return $message->reply($youtubeResponse);

            case 'test':
                $vectorReturn = new VectorQueryReturn(new OpenAICore());
                return $vectorReturn->vectorQueryReturnTest($message, $client);


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
                $activeCommand = (new HelpCommand);
                return $activeCommand->index($message, $content);

            //earmuffs (respond to owner only)
            case 'earmuffs':
                if ($owner) {
                    if ($commandArg === 'on') {
                        $anne = Anne::all()->first()->earmuffsToggle(true);
                        $anne->save();
                        Log::channel('db')->debug("on - " . json_encode($anne, 128));
                        return $message->reply("Earmuffs are on.");
                    } elseif ($commandArg === 'off') {
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
                    if ($commandArg === 'on') {
                        $anne = Anne::all()->first()->debugToggle(true);
                        $anne->save();
                        Log::channel('db')->debug("on - " . json_encode($anne, 128));
                        return $message->reply("Debug mode is on.");
                    } elseif ($commandArg === 'off') {
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
                $embed = $vecThing->wordVec($commandArg);
                $time2 = Carbon::now();
                $timeDiff = $time1->diffInMilliseconds($time2);
                $output =
                    "Input: $commandArg\n
                    Output: " . json_encode($embed) . "\n
                    Took: " . $timeDiff . "ms";
                return $message->reply($output);
                break;

            //lichess command
            //TODO: have anne just catch this
            case 'chess':
                $lichess = new Lichess;
                try {
                    if (stripos($commandArg, 'lichess') !== false) {
                        $lichessOutput = $lichess->exportGame(Lichess::getLichessGameId($commandArg));
                    }
                    return $message->reply($lichessOutput);
                } catch (\Exception $e) {
                    return $message->reply("Invalid lichess link.");
                }
                break;

                //for testing anne's ability to deduce what command to use on a message
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

                if($commandArg == 'top') {
                    Log::channel('db')->debug('top list');

                    return Playlist::controller($discord, $message, $commandArg);

                } else {
                    Log::channel('db')->debug('regular');
                    return self::createPlaylistMessage($discord, $message, 1, $default);
                }
                break;


            case 'trivia':
                $commandArg = ltrim($commandArg);

                if($commandArg==='top'){
                    $trivia = new TriviaScores;
                    $topPlayers = $trivia->getTopScores();

                   return $message->channel->sendMessage($topPlayers);
                   break;
                }

               $game = new TriviaCore;
               $gameStatus = $game->initGame($discord, $message);

                   if ($gameStatus['error']) {
                       return $message->reply($gameStatus['message']);
               }elseif($gameStatus['game']){
                   $questionOutput =
                       "Round " . $gameStatus['game']->round . "\nQuestion: " . $gameStatus['game']->question . "\n";
                   return $message->channel->sendMessage($questionOutput);

               }


//                $out = $trivia->init($message);
//                for($i=0; $i<strlen($out);$i+=1999) {
//                    $message->channel->sendMessage(substr($out,$i,1999));
//                }
//                return $message->channel->sendMesssage('k');
            break;

            case 'broken':
               $fart = new Search();
               $fart = $fart->getSearchResults($commandArg);

return $message->channel->sendMessage($fart);
                break;

            case 'fart':
                $dbFart = self::databaseData();
                $prompt = "You are a database query bot. You have been asked to query the database.\n";
                $prompt .= "The dialect is MYSQL.\n";
                $prompt .= "Here is a list of database tables you have available to query: \n$dbFart\n";
                $prompt .= "You are going to be given a statement in plain english, your job is to return a MYSQL query that will accomplish what the user is asking for.\n";
                $prompt .= "You can only respond with the SQL query, nothing else.\n";
                $prompt .= "Here is the user's statement:\n $commandArg\n";

//                $client = OpenAI::client(getenv('OPENAI_API_KEY'));
//                $response = $client->completions()->create([
//                    'prompt' => $prompt,
//                    'temperature' => 0.9,
//                    'max_tokens' => 150,
//                    'top_p' => 1,
//                    'frequency_penalty' => 0.0,
//                    'presence_penalty' => 0.6,
//                ]);

                return $message->channel->sendMessage($prompt);

            case 'compare':


                $output = "Input 1: $commandArray[1]
                Input 2: $commandArray[2]
                Levenshtein:" . levenshtein($commandArray[1], $commandArray[2]) . "\n
                Similar Text:" . similar_text($commandArray[1], $commandArray[2], $percent) . "\n
                Similar Percent: $percent\n
                Metaphone: " . metaphone($commandArray[1]) . " - " . metaphone($commandArray[2]) . "\n
                Metaphone Levenshtein: " . levenshtein(metaphone($commandArray[1]), metaphone($commandArray[2])) . "\n
                ";

                return $message->reply($output);


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

            //Label Row
            if (!$default) {
                $userLabelEmbed = new Embed($discord);

                $listUser = $discord->users->get('id', $message->mentions->first()->id);
//                $url='https://cdn.discordapp.com/avatars/' . $listUser->id . '/' . $listUser->avatar . '.webp?size=16';


                //TODO: emojis aren't deleting after being made and can't seem to get id out of the callback
//                // nab avatar and save local
//                $downloadImageData = file_get_contents($listUser->avatar);
//                file_put_contents('avatarEmoji.jpg', $downloadImageData);
//
//                // create emoji
//                $uploadImageData = file_get_contents('avatarEmoji.jpg');
//
//                $message->guild->createEmoji([
//                    'name' => 'playlistUser',
//                    'image' => 'data:image/jpeg;base64,' . base64_encode($uploadImageData)
//                ])->then(function ($emoji) use ($message){
//                    $emojiId = $emoji->id;
//                    $message->channel->sendMessage('<:playlistUser:'.$emojiId .'>');
//                    $emoji->delete();
//                });

//                $userLabelEmbed->setThumbnail($listUser->avatar.'?size=128');


                $userLabelEmbed->setDescription($listUser->username . ' - Total songs posted: ' . count($retrievedList['embeds']) . ' - Rank: #blah/blah');
            }

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
                    $playlistMessage->addEmbed($embed);
                    if(!$default){
                        $playlistMessage->addEmbed($userLabelEmbed);
                    }
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

        return $message->channel->sendMessage($messageWithPaginator);

    }

    public static function databaseData(){
        $tableArray = DB::select('SHOW TABLES');
        $tables = [];
        foreach($tableArray as $table){
            $tables[] = $table->{'Tables_in_'.getenv('DB_DATABASE')};
        }
        return json_encode($tables,128);
    }

}
