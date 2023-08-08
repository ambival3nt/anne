<?php

namespace App\Enums;


use App\Core\OpenAI\Prompts\analyzeUserInput;

class AnneActions {

    public static function list(){
        return [
            '-like',
            '-dislike',
            '-yoot',
            '-save',
            '-websearch',
            '-recall',
            '-get',
            '-ban',
        ];
    }

    public static function checkForAction($promptWithoutTag, $message){
        $command = new analyzeUserInput();
        //commandList is a string list of commands that the bot has decided are appropriate to use
        $commandList = $command->actions($promptWithoutTag, $message->author->username);

        $anneActionList = AnneActions::list();

        foreach($anneActionList as $action){
            if(stripos($commandList, $action)){
                $message->channel->sendMessage('```I have the urge to '.$action.'```');
            }
        }
    }
}
