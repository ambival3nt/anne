<?php

namespace App\Core\Memory;

use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use Illuminate\Support\Facades\Log;

class messageHistoryHandler
{
    //Adds the most recent message to the prompt as well as some other stuff i should put in their own classes or functions
    public static function addMostRecentMessage($prompt, $person, $personNameShown, $message, $userAliasList)
    {

        //alias list
        $aliasListString = substr(implode(', ', $userAliasList), 0, -1);
        $anneMessages = new AnneMessages;
        $lastMessageId = $anneMessages->latest('id')->first()->input_id;
        $lastMessage = Messages::find($lastMessageId);

        $actualLastMessage = $lastMessage->message;
        $lastPerson = Person::find($lastMessage->user_id);

        if($lastMessage !== $person->last_message) {
            $prompt .= "          \nThe last message you received was: $actualLastMessage from $lastPerson->name\n.";


        }
        if($message->author->id !== $person->id){
        $prompt = $prompt . "\n\nThe person you're speaking to now is $personNameShown, please refer to them by that name.\n";
            $prompt .= "\nThe last thing $personNameShown said to you was: $person->last_message\n";
        }else{
            $prompt = $prompt. "That's who you are speaking to now. $lastPerson->name\n.";
        }

                         $prompt = $prompt. "Your response was: $person->last_response\n";


        $prompt .= "The person you are speaking to has used the following names:\n
         $aliasListString\n
         , use that list of names to help you identify them.\n";


        $mergeToWindow = [
            'last_message' => $actualLastMessage,
            'last_message_user' => $lastPerson->name,
            'last_message_user_id' => $lastMessage->user_id,
            'last_message_from_current' => $person->last_message,
        ];

        return [
            'prompt'=>$prompt,
            'brain'=>$mergeToWindow,
            ];
    }

}
