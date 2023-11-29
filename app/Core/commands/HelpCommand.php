<?php

namespace App\Core\commands;

class HelpCommand
{
    public static function index($message, $content){

        $output = "# Hi, I'm Anne.\n";
        $output .= "To speak to me, just type 'anne,' or 'Anne,' or 'aNnE,' or whatever followed by a space and your message. __Don't forget the comma__.\n";
        $output .= "You can also __tag__ me, or __reply__ to me and I'll respond.\n";
        $output .= "If you want me to generate an image, type `anne show me ` followed by whatever you want me to generate. __No comma.__\n\n";
        $output .= "__Available Commands__:\n";
        $output .= "*All of these start with* `-=`. *So for instance,* `-=help` *is how you got here.*\n";
        foreach(self::commandArray() as $command => $description){
            $output .= "\t`$command`\n";
            $output .= "\t\t$description\n";
        }
        $output .= "";
        return $message->reply($output);
    }

    //commands are from the switch in HandleCommandProcess.php
    public static function commandArray()
    {
        return [
            'help'              => "Uh... you're looking at it.",
            'ping'              => "Pong!",
            'yoot'              => "Search for something on youtube, get the first result back.",
            'trivia'            => "Starts a game of trivia.",
            'chess'             => "Displays a valid lichess replay link as a gif.",
            'command'           => "Debugging command, shows you what command anne would try to run on a message.",
            'playlist'          => "Displays all song links posted today. Can be followed with a user tag to display a specific user's lifetime playlist, or with 'top' to display the top 10 song posters.",
            'embed'             => "Embed a message into a vector. For some reason.",
            'debug'             => "Debugging output on/off. Follow command with on or off. Only works for authorized users.",
            'earmuffs'          => "Makes anne only listen to whoever is responsible for her. Follow command with on or off. Only works for authorized users.",
            'spam'              => "Makes anne vomit out the JSON for whatever message follows the command.",
            'think'             => "Makes anne analyze a message for various abstract concepts.",
            'test'              => "Get the vector similarity results for whatever message follows the command.",
            'compare'           => "Compare the two words that follow the command using levenstein distance and metaphone values.",
            'fart'              => "Does whatever i'm using it to test for at the moment.",

        ];
    }
}
