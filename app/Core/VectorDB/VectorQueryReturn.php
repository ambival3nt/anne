<?php

namespace App\Core\VectorDB;

use App\Core\OpenAI\OpenAICore;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use Illuminate\Support\Facades\Log;

class VectorQueryReturn
{
    private OpenAICore $openAICore;

    public function __construct(OpenAICore $openAICore)
    {
        $this->openAICore = $openAICore;
    }

    /**
     * @param $message
     * @return mixed
     * This is the output for the vector test list that you get from the test command. No effect on regular response.
     */
    public function vectorQueryReturnTest($message): mixed
    {
        $promptRemoveTag = substr($message->content, 6);

        //get vector for user's message
        $userEmbed = $this->openAICore->buildEmbedding($promptRemoveTag)->embeddings[0]->embedding;

        // Query pinecone with the user embedding
        $vectorQueryResult = new PineconeCore;
        $vectorQueryResult = $vectorQueryResult->query($userEmbed);

        //format the output
        $mask = "|%5.5s | %10.10s | %10.10s | %-20.20s | %-55.55s |\n";
        $anneOutput = "```\n
        Input: $promptRemoveTag\n" . sprintf($mask, 'Id', 'Score', 'Date', 'User', 'Message');

        foreach ($vectorQueryResult['matches'] as $result) {

            //anne

            //if its one of anne's message vectors...
            if ($result->metadata->anne) {

                //This is hella broken we gotta fix this shit sooner rather than later TODO: fuckin fix this sooner rather than later
                $id = substr($result->id, 5);
                $anneMessageModel = new AnneMessages;

                //find it and grab the message, its doing it right now by cutting up the id string which is bad
                $messageData = $anneMessageModel->where('input_id', $id)->first()->toArray() ?? [];

                if (!$messageData) {
                    Log::debug('Missing messageData on id: ' . $id);
                    continue;
                }

                $messageOutput = trim($messageData['message']) ?? 'Could not load message.';
                $user = "anne.hedonia";
                $id = "anne-" . $messageData['id'];

                //if its a user's message vector...
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

            $score = $result->score;
            $date = $result->metadata->dateTime;

            $anneOutput = $anneOutput . sprintf($mask, $id, $score, $date, $user, $messageOutput);

        }
        $anneOutput = $anneOutput . "```";

        return $message->reply($anneOutput);
    }
}
