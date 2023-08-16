<?php

namespace App\Core\Memory;

use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class messageHistoryHandler
{
    //Adds the most recent message to the prompt as well as some other stuff i should put in their own classes or functions
    public static function addMostRecentMessage($prompt, $person, $personNameShown, $message, $userAliasList)
    {
try {
    //alias list
    $aliasListString = substr(implode(', ', $userAliasList), 0, -1);
    $anneMessages = new AnneMessages;
    $lastMessageId = $anneMessages->latest('id')->first()->input_id;
    $lastMessage = Messages::find($lastMessageId);

    $actualLastMessage = $lastMessage->message;
    $lastPerson = Person::find($lastMessage->user_id);

//    if ($lastMessage !== $person->last_message) {
//        $prompt .= "          \nThe last message you received was: $actualLastMessage from $lastPerson->name\n.";
//    }
//    if ($message->author->id !== $person->id) {
        $prompt = $prompt . "\n\nThe person you're speaking to now is $personNameShown, please refer to them by that name.\n";
        $prompt .= "\nThe last thing $personNameShown said to you was: $person->last_message\n";
//    } else {
//        $prompt = $prompt . "That's who you are speaking to now. $lastPerson->name\n.";
//    }

    $prompt = $prompt . "Your response was: $person->last_response\n";


    $prompt .= "The person you are speaking to has used the following names:\n
         $aliasListString\n
         , use that list of names to help you identify them.\n";

    $messages = Messages::all()->take(-5);

    $historyString = "\n\nThis is the current, most recent chat history for the whole server:\n\n";


    foreach($messages as $userMessage){

        $historyString .= "Timestamp: " . Carbon::parse($userMessage->created_at)->toDateTimeString() . "\n" .
            $userMessage->user->name . ' said: ' . $userMessage->message . "\n";

        $historyString .= $userMessage->anneReply ?
            "You replied: " .  $userMessage->anneReply->message . "\n\n"
            : "You did not reply.";


};
    $historyString .= "\nReference any of these messages to enrich your response, or address the other users mentioned in the history if its relevant.\n\n";
    $prompt .=  $historyString;
}catch(\Exception $e){
    Log::channel('db')->debug($e->getMessage());
}
        return [
            'prompt'=>$prompt,
//            'brain'=>$mergeToWindow ?? [],
            ];
    }

}
