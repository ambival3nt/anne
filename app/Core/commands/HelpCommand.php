<?php

namespace App\Core\commands;

class HelpCommand
{
    public static function index($message, $content){
        return $message->reply('Soon. Yes, soon. For now consult your local psychiatric ward.');
    }
}
