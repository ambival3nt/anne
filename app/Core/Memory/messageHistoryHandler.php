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

        $aliasListString = "";
        try {

            // get alias list
            if ($userAliasList) {
                $aliasListString = substr(implode(', ', $userAliasList), 0, -1);
            }
            $anneMessages = new AnneMessages;
            $lastMessageId = $anneMessages->latest('id')->first()->input_id;    // this is PRIOR message id, not current message
            $lastMessage = Messages::find($lastMessageId);                      // this is PRIOR message object, not current message
            $lastMessageText = $lastMessage->message;                           // this is PRIOR message text, not current message
            $lastPerson = Person::find($lastMessage->user_id);                  // this is PRIOR message user, not current message user

            $anneDebug =  [
                'lastMessageId' => $lastMessageId,
                'lastMessage' => $lastMessage,
                'lastMessageText' => $lastMessageText,
                'lastPerson' => $lastPerson,
                ];

            //new user handler
            if (!$lastPerson->last_message) {
                $person->last_message = "Anne, I am a new user. My name is $personNameShown.";
                $person->save();
                Log::debug('Added new user ' . $personNameShown . 'to message history table.');
            }

            // if the last message isn't this person's last message...
            if ($lastMessageText !== $person->last_message) {
                $prompt .= "          \nThe last message you received was from \n\n$lastPerson->name, who said: \n$lastMessageText\n-----";
            }


            $prompt = $prompt . "\n\nThe person you're speaking to now is $personNameShown, please refer to them by that name.\n";

            $prompt .= "They are also known by the following names:\n
         $aliasListString\nUse this to help connect your chat history with the person";


                $prompt .= "\nThe last thing this person ($person->name) said to you was: $person->last_message\n";
                $prompt = $prompt . "Your response was: $person->last_response\n";

//$message->channel->sendMessage(json_encode($anneDebug));
Log::debug(json_encode($anneDebug,128));

            $messages = Messages::all()->take(-5);

            $historyString = "\n\nThis is the most recent chat history, including your replies:\n\n";




            foreach ($messages as $userMessage) {

                $historyString .= "Timestamp: " . Carbon::parse($userMessage->created_at)->toDateTimeString() . "\n" .
                    $userMessage->user->name . ' said: ' . $userMessage->message . "\n";

                $historyString .= $userMessage->anneReply ?
                    "You replied: " . $userMessage->anneReply->message . "\n\n"
                    : "You did not reply.";


            };
            $historyString .= "\nReference any of these messages to enrich your response, or address the other users mentioned in the history if its relevant.\n\n";
            $prompt .= $historyString;
        } catch (\Exception $e) {
            Log::channel('db')->debug($e->getMessage());
}
        return [
            'prompt'=>$prompt,
//            'brain'=>$mergeToWindow ?? [],
            ];
    }

}
