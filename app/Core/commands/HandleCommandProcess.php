<?php

namespace App\Core\commands;


use App\Models\Anne;

class HandleCommandProcess
{

    public static function isValidCommand($command){
    $commandList = [
        'ping',
        'help',
        'earmuffs',
    ];

    return in_array($command, $commandList, true);
    }
    public static function runCommandOnContent($command, $content, $message, $owner){
        switch($command){
            case 'ping':
            return $message->reply("Oh I'll ping you, pal.");
            case 'help':
                $activeCommand = (new HelpCommand)->index($message, $content);
                return $activeCommand->index($message, $content);
            case 'earmuffs':
                if($owner) {
                    if ($content === 'on') {
                        $anne = Anne::find(1)->earmuffs(true);
                        $anne->save();
                        return $message->reply("Earmuffs are on.");
                    } elseif ($content === 'off') {
                        $anne = Anne::find(1)->earmuffs(false);
                        $anne->save();
                        return $message->reply("Earmuffs are off.");
                    } else {
                        return $message->reply("Earmuffs are currently " . Anne::find(1)->earmuffs);
                    }
                }else break;
    }
}

}
