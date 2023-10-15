<?php

namespace App\Core\VectorDB;

use App\Core\OpenAI\OpenAICore;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
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
        $stupidArray = [];
        try {

            //only use vectors with score above the threshhold (hardcoded to .79 for now, this will eventually move to front end)
            foreach ($resultArray['matches'] as $result) {
                if ($result->score < 0.82) {
                    continue;
                }
                $isAnne = false;
                //if it's anne's message vector

                if (data_get($result, 'metadata.anne', false) !== false) {
                    $isAnne = true;
                    //get message id
                    $id = substr($result->id, 5);
                    try {

                        if ($id) {
                            $priorMessageData = Messages::find($id) ?? null;
                            if ($priorMessageData) {
                                $priorMessageData = $priorMessageData->toArray() ?? [];
                                $priorMessageOutput = trim($priorMessageData['message']) ?? 'Could not load prior user message from anne message.';
                                $priorMessageUser = Person::find(trim($priorMessageData['user_id']))->name ?? '';
                            } else {

                                $priorMessageData = [];
                                $priorMessageUser = '';
                                $priorMessageOutput = '';
                            }

                        }
                        $anneMessage = $priorMessageData->anneReply ?? '';
//                        $message->channel->sendMessage(json_encode($result, 128) . "\nMessage: $priorMessageOutput\nUser: $priorMessageUser\nAnne: $anneMessage");
                    } catch (\Exception $e) {
                        Log::channel('db')->debug($e->getMessage() . ' on line ' . $e->getLine());
                    }
                    //grab anne message model, query it for that message id, pull result
                    $anneMessageModel = new AnneMessages;
                    $messageData = $anneMessageModel->where('input_id', $id)->first() ?? null;

                    if ($messageData) {
                        $messageData = $messageData->toArray() ?? [];
                    } else {
                        Log::channel('db')->debug('Missing messageData on id: ' . $id);
                        continue;
                    }

                    //trim the message and put 'you' as the user (because anne said it and its a prompt to her)
                    $messageOutput = trim($messageData['message']) ?? 'Could not load message.';
                    $user = "You";

                    //Output is like: [HH:MM:SS MM/DD/YY] Username: message, and for anne messages we include the message that it is a reply to

                    if (data_get($priorMessageData, 'created_at', false) !== false) {
                        $datestamp = Carbon::parse($priorMessageData['created_at'])->toDateTimeString();
                    } else {
                        $datestamp = "Date Unknown";
                    }

                    $vectorPrompt .= "\n" .
                        $date = '[' . $datestamp . '] ' . $priorMessageUser . ": '" . $priorMessageOutput;

                    $vectorPrompt .= "\n" .
                        $date = '[' . $result->metadata->dateTime . '] ' . $user . ": '" . $messageOutput;

                    //if its a user's message vector...
                } else {
                    try {
                        $id = $result->id;
                        $messageModel = new Messages;
                        $messageData = $messageModel->with('anneReply')->where('id', $id)->first();
                        if (!$messageData) {
                            Log::channel('db')->debug('Missing messageData on id: ' . $id);
                            continue;
                        }
                        //same shit BUT we need to grab anne's response too, which I'm pretty sure I set up a relationship for
                        $anneReplyMessage = $messageData->anneReply ?? null;


                        $messageOutput = $messageData['message'] ?? 'Could not load message.';
                        $people = new Person;
                        $user = $people->where('id', $messageData['user_id'])->first()->name ?? '??';

                        //same shit as anne's but the inverse, we include anne's message after the fact
                        $vectorPrompt .= "\n" .
                            $date = '[' . $result->metadata->dateTime . '] ' . $user . ": '" . $messageOutput;


                    } catch (\Exception $e) {
                        Log::channel('db')->debug('Vector Parsing Exception: ' . $e->getMessage() . ' on ' . $e->getLine());
                    }
                    if ($anneReplyMessage) {
                        $vectorPrompt .= "\n" .
                            $date = '[' . Carbon::parse(data_get($anneReplyMessage, 'created_at', null)->toDateTimeString()) . '] ' . 'You: ' . trim($anneReplyMessage->message);
                    } else {
                        Log::channel('db')->debug('Missing Anne response for messageId #' . $id . ". Expected: " . (string)$messageData->anneReply->id ?? '[!missing!]');
                    }
                }


            }


        } catch (\Exception $e) {
            //handle errors, send to log (eventually on frontend)
            Log::channel('db')->debug($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            Log::channel('db')->debug("Vector prompt preload error.");
        }
        //get summary from other model
        $summarized = self::summarizeVectorResult($vectorPrompt, $message, $client);

        //Take the pre-prompt, which already has the user input, add some instructions for this, attach summarized chat history, return to main function
        $result = "The following are your memories, from your perspective, of past conversations. In this instance, the first person (I) is you referring to yourself:\n"
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


        $summaryPrompt = "You are the memory of someone named Anne. You will be provided with discord chat history, and your job is to summarize t.\n\n
        As this is your memory, and you are technically anne, you can use the word 'I' to refer to yourself.\n\n
        Refer to users as the names provided. If a user's name is not provided, you may refer to them as 'they' or 'them'.\n\n
        Include dates if possible.\n\n
        Here is the chat history you are summarizing:\n\n
        $vectorPrompt\n\n
        When you have summarized the memories, please review your summary, make sure it is in chronological order, and return the summary for anne to decide what to say.\n\n
        This is very important, she is counting on you.\n\n
        ";

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
    public function vectorQueryReturnTest($message, $client): mixed
    {

        $promptRemoveTag = substr($message->content, 6);

        //get vector for user's message
        $userEmbed = $this->openAICore->buildEmbedding($promptRemoveTag, $client)->embeddings[0]->embedding;

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
                    Log::channel('db')->debug('Missing messageData on id: ' . $id);
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
                    Log::channel('db')->debug('Missing messageData on id: ' . $id);
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
