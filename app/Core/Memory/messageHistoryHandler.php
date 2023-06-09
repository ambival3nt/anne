<?php

namespace App\Core\Memory;

use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;

class messageHistoryHandler
{
    //Adds the most recent message to the prompt as well as some other stuff i should put in their own classes or functions
    public static function addMostRecentMessage($prompt, $person, $personNameShown)
    {

        $anneMessages = new AnneMessages;
        $lastMessageId = $anneMessages->latest('id')->first()->input_id;
        $lastMessage = Messages::find($lastMessageId);

        $actualLastMessage = $lastMessage->message;
        $lastPerson = Person::find($lastMessage->user_id);

        if($lastMessage !== $person->last_message) {
            $prompt .= "          \nThe last message you received was: $actualLastMessage from $lastPerson->name\n";
        }
        $prompt = $prompt . "\n\nThe person you're speaking to now is $personNameShown, please refer to them by that name.\n
                        \nThe last thing $personNameShown said to you was: $person->last_message\n
                         \nYour response to them was: $person->last_response\n";

        if($personNameShown !== $person->name) {
            $person->nameMapping($personNameShown);
        }
        return $prompt;
    }

    public static function addMostRecentMessageGPT($person, $personNameShown)
    {

        $output = [];
        $anneMessages = new AnneMessages;
        $lastMessageId = $anneMessages->latest('id')->first()->input_id;
        $lastMessage = Messages::find($lastMessageId);

        $actualLastMessage = $lastMessage->message;
        $lastPerson = Person::find($lastMessage->user_id);

        $output[] =  [
            'role'=>'user',
            'content' => "The person you're speaking to has username $person->name. But please refer to them as $personNameShown.\n",
        ];
        $output[] = [
            'role'=>'user',
            'content'=> $person->last_message
        ];
        $output[] = [
            'role'=>'user',
            'content'=> $person->last_response,
        ];

        if($lastMessage !== $person->last_message){
            $output[] = [
                'role'=>'user',
                'content'=>"The last message you received was: $actualLastMessage from $lastPerson->name"
            ];
        }else{
            $output[] = [
                'role'=>'user',
                'content'=>"That was the last message you receieved."
            ];
        }

        return $output;
    }

}
