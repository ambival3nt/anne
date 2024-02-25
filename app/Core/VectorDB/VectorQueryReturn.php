<?php

namespace App\Core\VectorDB;

use App\Core\OpenAI\OpenAICore;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use App\Utilities\Stringscord;
use Discord\Parts\Channel\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class VectorQueryReturn
{
    private OpenAICore $openAICore;

    public function __construct(OpenAICore $openAICore)
    {
        $this->openAICore = $openAICore;
    }

   public static function addHistoryFromVectorQuery(array $resultArray, $message=null, $client = null, OpenAICore $instance=null)
{
    $vectorPrompt = "";
    $priorMessageData = [];
    $priorMessageUser = '';
    $priorMessageOutput = '';
    $threshold = 0.21; // This could be a class constant or a configuration value

    try {
        foreach ($resultArray as $result) {
            if (data_get($result, 'distances') > $threshold) {
                continue;
            }
            $isAnne = false;

            if (data_get($result, 'metadatas.anne', false) !== false) {
                $isAnne = true;
                $id = data_get($result, 'metadatas.id', null);
                try {
                    if ($id) {
                        $priorMessageData = AnneMessages::where('input_id', $id)->userMessage->get() ?? null;
                        if ($priorMessageData) {
                        $message->reply(json_encode($priorMessageData));
                            $priorMessageData = $priorMessageData->toArray() ?? [];
                            $priorMessageOutput = trim($priorMessageData['message']) ?? 'Could not load prior user message from anne message.';
                            $priorMessageUser = Person::find(trim($priorMessageData['user_id']))->name ?? '';
                        } else {
                            $priorMessageData = [];
                            $priorMessageUser = '';
                            $priorMessageOutput = '';
                        }
                    }
                    $anneMessage = Messages::find($id) ?? null;
                } catch (\Exception $e) {
                    Log::channel('db')->debug($e->getMessage() . ' on line ' . $e->getLine() . ' Trace: ' . $e->getTraceAsString());
                }
            }

            $messageModel = $isAnne ? new AnneMessages : new Messages;
            $messageData = $messageModel->where('id', $id)->first();

            if ($messageData) {
                $messageData = $messageData->toArray() ?? [];
            } else {
                Log::channel('db')->debug('Missing messageData on id: ' . $id);
                continue;
            }

            $message->reply(json_encode($priorMessageData,128));

            $messageOutput = trim($messageData['message']) ?? 'Could not load message.';
            $user = $isAnne ? "You" : (new Person)->where('id', $messageData['user_id'])->first()->name ?? '??';

            if (data_get($priorMessageData, 'created_at', false) !== false) {
                $datestamp = Carbon::parse($priorMessageData['created_at'])->toDateTimeString();
            } else {
                $datestamp = "Date Unknown";
            }

            $vectorPrompt .= "\n" . '[' . $datestamp . '] ' . $priorMessageUser . ": '" . $priorMessageOutput;
            $vectorPrompt .= "\n" . '[' . data_get($result,'metadatas.dateTime', null) . '] ' . $user . ": '" . $messageOutput;
        }
    } catch (\Exception $e) {
        Log::channel('db')->debug($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . ' Trace: ' . $e->getTraceAsString());
        Log::channel('db')->debug("Vector prompt preload error.");
    }

    $summarized = self::summarizeVectorResult($vectorPrompt, $message, $client);

    new Stringscord($message, json_encode($summarized));

    $result = "The following are your memories, from your perspective, of past conversations. It is possible that you have no memories, and that is fine. In this instance, the first person (I) is you referring to yourself:\n"
        . $summarized;
    $result .= "\n-----\n";
    return [
        'result' => $result,
        'summary' => $summarized,
    ];
}
    public static function summarizeVectorResult(string $vectorPrompt, $message=null, $client)
    {

        // should only be null when initializing the prompt table for the prompt editor, otherwise it should be a message object
        if($message!==null) {
            $userInput = $message->content ?? '';
            $mention = $message->mentions->first() ?? null;

            if ($mention) {
                $userInput = str_replace('@' . $mention->id, 'anne', $message->content);
            } elseif (str_starts_with(strtolower($message->content), "anne,") && !$message->author->bot) {
                $userInput = substr($message->content, 5);
            }
        }

            // This prompt is what create's anne's memories, it gets attached to her message history and she is told what it is
        $summaryPrompt = "You are the memory of someone named Anne. You will be provided with discord chat history, and your job is to summarize it.\n\n
        As this is your memory, and you are technically anne, you can use the word 'I' to refer to yourself.\n\n
        Refer to users as the names provided. If a user's name is not provided, you may refer to them as 'they' or 'them'.\n\n
        Include dates if possible.\n\n
        ";

        //if there are memories
        if(strlen($vectorPrompt)>0) {
            $message->reply('i have chat history! it is: ' . $vectorPrompt);

            $summaryPrompt .=
                "Here is the chat history you are summarizing:\n\n
        $vectorPrompt\n\n
        When you have summarized the memories, please review your summary, make sure it is in chronological order, and return the summary for anne to decide what to say.\n\n
        This is very important, she is counting on you.\n\n
        ";
        }else{
            //if there are no memories
            $summaryPrompt .= "You have no chat history to summarize. Mention this.\n\n";
        }
        // This should end up as a memory summary from anne's perspective
        $result = $client->completions()->create([
                'model' => 'gpt-3.5-turbo-instruct',
                'prompt' => $summaryPrompt,
                'max_tokens' => 600,
                'stop' => [
                    '-----',
                ],
                'n' => 1,
            ]
        );

        $return = $result['choices'][0]['text'];

        return $return;

        }

        /**
         * @param $message
         * @return mixed
         * This is the output for the vector test list that you get from the test command. No effect on regular response.
         */
        public function vectorQueryReturnTest(Message $message, $client): mixed
        {
            $removeTagLength = 6; // This could be a class constant or a configuration value
            $promptRemoveTag = substr($message->content, $removeTagLength);

            try {
                $userEmbed = $this->openAICore->buildEmbedding($promptRemoveTag, $client)->embeddings[0]->embedding;
            } catch (\Exception $e) {
                Log::channel('db')->debug($e->getMessage() . ' on line ' . $e->getLine() . ' Trace: ' . $e->getTraceAsString());
            }

            $vectorQueryResult = new ChromaCore();
            $vectorQueryResult = $vectorQueryResult->query($userEmbed);

            $mask = "|%5.5s | %10.10s | %10.10s | %-20.20s | %-40.40s |\n";
            $anneOutput = "```\n
                Input: $promptRemoveTag\n" . sprintf($mask, 'Id', 'Score', 'Date', 'User', 'Message');

            foreach (data_get($vectorQueryResult, 'data', []) as $result) {

                $id=data_get($result, 'metadatas.id', null);
                $messageOutput = '';
                $messageData = [];

                $messageModel = new Messages;
                $messageData = $messageModel->where('id', $id)->first()->toArray() ?? null;

                if (!$messageData) {
                    Log::channel('db')->debug('Missing messageData on id: ' . $id);
                    continue;
                }

                if ($messageData) {
                    $messageOutput = data_get($messageData, 'message', 'No message found.') ?? 'Could not load message.';
                }

                $userId=data_get($messageData, 'user_id', -2);

                $people = new Person;

                if($userId==-1){
                    $user = 'anne.hedonia';
                }else {
                    $user = $people->where('id', data_get($messageData, 'user_id', null))->first()->name ?? '??';
                }

                $score = data_get($result, 'distances', '0.0');
                $date = data_get($result, 'metadatas.dateTime', 'Unknown');

                $anneOutput = $anneOutput . sprintf($mask, $id, $score, $date, $user, $messageOutput);

            }
            $anneOutput = $anneOutput . "```";

            return $message->reply($anneOutput);
        }
    }
