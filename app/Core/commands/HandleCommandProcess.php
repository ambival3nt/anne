<?php

namespace App\Core\commands;


use App\Core\Features\Lichess;
use App\Core\OpenAI\Prompts\analyzeUserInput;
use App\Models\Anne;
use App\Models\Playlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use phpw2v\Word2Vec;

class HandleCommandProcess
{

    public static function isValidCommand($command)
    {
        Log::debug("command in: " . $command);
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
        ];

        return in_array($command, $commandList, true);
    }

    public static function runCommandOnContent($command, $content, $message, $owner, $commandArray, $discord)
    {
        Log::debug("Command: $command\nContent: $content\nMessage: $message\nOwner: $owner");

        $arg = "";

        foreach ($commandArray as $index => $commandPart) {
            if ($index === 0) {
                $command = $commandPart;
            } else {
                $arg = $commandPart;
            }
        }


        switch ($command) {

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
                        Log::debug("on - " . json_encode($anne, 128));
                        return $message->reply("Earmuffs are on.");
                    } elseif ($arg === 'off') {
                        $anne = Anne::all()->first()->earmuffsToggle(false);
                        $anne->save();
                        Log::debug("off - " . json_encode($anne, 128));
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
                        Log::debug("on - " . json_encode($anne, 128));
                        return $message->reply("Debug mode is on.");
                    } elseif ($arg === 'off') {
                        $anne = Anne::all()->first()->debugToggle(false);
                        $anne->save();
                        Log::debug("off - " . json_encode($anne, 128));
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
                return self::extractSelfCommand($commandArray, $command, $message);
                break;

            //test command for playlist functionality
            case 'playlist':
                $playlist = new Playlist;
                return $message->channel->sendEmbed(
             $playlist->getListForToday($discord));
                break;

            default:
                return $message->reply("Uh... sure.");
        }

    }

    /**
     * @param $commandArray
     * @param analyzeUserInput $command
     * @param $message
     * @return mixed
     */
    protected static function extractSelfCommand($commandArray, analyzeUserInput $command, $message): mixed
    {
        $commandBody = array_shift($commandArray);
        $commandBody = implode(" ", $commandArray);
        $command = new analyzeUserInput();
        $commandList = $command->actions($commandBody, $message->author->username);
        return $message->reply($commandList);
    }

}
