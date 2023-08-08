<?php

namespace App\Core\OpenAI;

use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Psr\Http\Message\ResponseInterface;

class GPTCore
{
    public function init($discord, $message, $person, $promptRemoveTag, $userEmbed)
    {



    }

    private function addHistoryFromVectorQueryGPT(array $resultArray)
    {
        $output = [];
        $output = ['role'=>'user', 'content'=>
            "Here are some related messages from your memory.\n
            If you think a message is not relevant to this conversation, you can ignore it",
        ];

        try {
            foreach ($resultArray['matches'] as $result) {
                if ($result->score < 0.79) {
                    continue;
                }

                //if its an anne message vector
                if (data_get($result, 'metadata.anne', false) !== false) {

                    //this is such a bad way to get anne messages like cman
                    $id = substr($result->id, 5);
                    $anneMessageModel = new AnneMessages;

                    $messageData = $anneMessageModel->where('input_id', $id)->first() ?? null;

                    if($messageData) {
                        $messageData = $messageData->toArray() ?? [];
                    } else {
                        Log::debug('Missing messageData on id: ' . $id);
                        continue;
                    }

                    $messageOutput = trim($messageData['message']) ?? '';
                    $output['content'] .= '\nYou said: ' . $messageOutput .
                        "' at this date and time: " . $date = $result->metadata->dateTime . "\n";


                    //user

                } else {
                    $id = $result->id;
                    $messageModel = new Messages;
                    $messageData = $messageModel->where('id', $id)->first()->toArray();
                    if (!$messageData) {
                        Log::debug('Missing messageData on id: ' . $id);
                        continue;
                    }
                    $messageOutput = $messageData['message'] ?? 'Could not load message.';
                    $people = new Person;
                    $user = $people->where('id', $messageData['user_id'])->first()->name ?? '??';

                }

                $output['content'] .=
                    "\n$user said: '" . $messageOutput .
                    "' at this date and time: " . $date = $result->metadata->dateTime . "\n";
            }
            $output['content'] .= "Please, carefully consider if any of the messages are relevant before using them to create your response.\n\n";

        } catch (\Exception $e) {
            Log::debug($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            Log::debug("Vector prompt preload error.");
            return $e->getMessage();
        }
        return $output;
    }


}
