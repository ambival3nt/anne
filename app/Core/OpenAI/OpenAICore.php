<?php

namespace App\Core\OpenAI;

use App\Core\config\CommonKnowledge;
use App\Core\GooseAI\GooseAICore;
use App\Core\Memory\messageHistoryHandler;
use App\Core\OpenAI\Prompts\analyzeUserInput;
use App\Core\OpenAI\Prompts\ZiggyBasilisk;
use App\Core\VectorDB\PineconeCore;
use App\Jobs\UpsertToPineconeJob;
use App\Models\Anne;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MathPHP\Statistics\Distance;
use OpenAI\Client;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAICore
{
    //This is the main query function for the bot, if it's going to be using OpenAI's API
    private analyzeUserInput $analyzeUserInputFormatted;

    public function __construct()
    {
        $this->analyzeUserInputFormatted = new analyzeUserInput();
    }

    public function query($message, $discord, $mention = null, $reply=null, $useGpt = false)
    {
        $anneModel = (new Anne())->all()->first();

        $prompt = "";

        //init query
        list(
            $prompt,
            $person,
            $gptPrompt
            ) = $this->initQuery($message, $useGpt);

        $promptRemoveTag = null;



//Test route for anne's brain///////////////////////////
        if (str_starts_with($message->content, "-=think") && !$message->author->bot) {
            try {
                $m = substr($message->content, 7);

                //Analyze the user's message for abstracts
                return $message->reply($this->analyzeUserInput($m, $message->author->displayname));

            } catch (\Exception $e) {
                return $message->reply('NOP sorry, something went wrong: ' . $e->getMessage());
            }
        }
//////////////////////////////////////////////////////////



// Vector scoring chart output/////////////////////////////////
        if (str_starts_with($message->content, "-=test")
            && !$message->author->bot
        ) {

            return $this->vectorQueryReturnTest($message);



            ///////////////////////////////////////////////////////




            //is someone mentionining anne? (evidently this covers replies too)
        } elseif ($mention) {
            $promptRemoveTag = str_replace('@' . $mention->id, 'anne', $message->content);

            //standard query
        } elseif (str_starts_with(strtolower($message->content), "anne,") && !$message->author->bot) {

            //chop tag off string
            $promptRemoveTag = substr($message->content, 5);
        }
        if ($promptRemoveTag) {
            try {

                //get vector for user's message
                $userEmbed = $this->buildEmbedding($promptRemoveTag)->embeddings[0]->embedding;

                //add any preload prompts etc
                $promptWithPreloads = $prompt . $promptRemoveTag;

                // Query pinecone with the user embedding
                $vectorQueryResult = new PineconeCore;
                $resultArray = $vectorQueryResult->query($userEmbed);

                //parse vectors into prompt
                $promptWithVectors = $this->addHistoryFromVectorQuery($resultArray, $promptWithPreloads) ?? "";


                //If using GPT api and format
                if($useGpt){
                    $message->reply('im dumb and using the gpt route');
                    $gptPrompt[] = [
                        'role'=>'user',
                        'content'=>$promptRemoveTag
                    ];

                    $gptPrompt[] = $this->addHistoryFromVectorQueryGPT($resultArray);

                    $gptPrompt[] = [
                        'role'=>'user',
                        'content'=>$promptRemoveTag
                    ];

                    $gptPromptJson = [];

                    foreach($gptPrompt as $gpt){
                        $gptPromptJson[] = json_decode(json_encode($gpt));
                    }
                }


                $promptWithVectors .= "\nUser says: $promptRemoveTag\n\n

                Anne says: ";

                //get davinci response

                if(!$useGpt){
//                    $result = OpenAI::completions()->create(['model' => 'text-davinci-003',
//                            'prompt' => $promptWithPreloads,
////                        'top_p' => .25,
//                            'temperature' => 0.75,
//                            'max_tokens' => 600,
//                            'stop' => [
//                                '-----',
//                            ],
//                            'frequency_penalty' => 0.4,
//                            'presence_penalty' => 1.2,
//                            'best_of' => 2,
//                            'n' => 1,
//                        ]
//                    );

                    $result = new GooseAICore();

                    $result = $result->gooseInit($promptWithVectors);

                    $responsePath = $result;
                    Log::debug($responsePath);
                }else{
                    //get gpt4 response
                    $result = OpenAI::chat()->create(['model' => 'gpt-3.5-turbo',
                            'messages' => $gptPromptJson,
//                        'top_p' => .25,
                            'temperature' => 0.5,
                            'max_tokens' => 600,
                            'stop' => [
                                '-----',
                                '<|endoftext|>',
                            ],
                            'frequency_penalty' => 0.5,
                            'presence_penalty' => 1,
//                        'best_of' => 3,
                            'n' => 1,


                        ]
                    );
                    $responsePath=$result->choices[0]->message->content;
                }


                //update person model
                $person->update([
                    'last_message' => $promptRemoveTag,
                    'last_response' => $responsePath,
                    'message_count' => $person->message_count + 1
                ]);


                //init message model
                $messageModel = new Messages();

                //build anne's embedding for pinecone
                $anneEmbed = $this->buildEmbedding($responsePath, true)->embeddings[0]->embedding;

                $messageModel = $messageModel->create([
                    'user_id' => (string)$person->id,
                    'message' => $promptRemoveTag,
                    'response' => $responsePath
                ]);

                //Get cosine similarity locally (for testing, maybe permanent)
                if($anneModel->debug) {
                    $cosineSimilarity = Distance::cosineSimilarity($userEmbed, $anneEmbed);
                }
                //send embeds to vector db
                $this->sendToPineconeAPI($userEmbed, ['id' => (string)$messageModel->id], (string)$person->id, $anneEmbed);

                //init Anne's message model
                $anneMessage = new AnneMessages();

                //add to anne's message archive
                $anneMessage = $anneMessage->create([
                    'user_id' => $person->id,
                    'message' => $responsePath,
                    'input_id' => $messageModel->id,
                    'anne_vector_index' => "anne-$messageModel->id",
//                    'vector' => json_encode($anneEmbed),
                ]);


            } catch (\Exception $e) {
                Log::debug($e->getMessage());
                return $message->reply($promptWithPreloads);
                //    return $message->reply('For some reason I cannot explain, I do not have an answer.');
            }

            //todo: if this debug shit all works move it to its own class to be invoked anywhere we need it
            if($anneModel->debug){
                $responsePath .= "\nCosine Similarity: " . $cosineSimilarity;
            }

            return $message->reply(substr($responsePath,0,1999));
        }elseif (str_starts_with($message->content, "anne show me ") && !$message->author->bot) {

            $result = OpenAI::images()->create([
                'prompt' => substr($message->content, 12),
                'n' => 1,
                'size' => "1024x1024"
            ]);


           $image = file_get_contents($result['data'][0]['url']);
           Log::debug(json_encode($image));
           $filename = 'discordUpload' . Carbon::now()->toDateTimeString().'.png';
           $put = file_put_contents($filename, $image);
           Log::debug(json_encode($put));
           $builder = MessageBuilder::new();

            $builder->addFile($filename);

            return $message->reply($builder);

//           return Discord::

//            return $message->reply($result['data'][0]['url']);
        }

        else{
            return "Something went wrong.";
        }

    }

    public function buildEmbedding($message)
    {

        $embedString = $message;

        $embedVector = OpenAI::embeddings()->create([
            'input' => $embedString,
            'model' => 'text-embedding-ada-002',
        ]);


        return $embedVector;

    }

    public function sendToPineconeAPI($vector, $data = null, $discordUserId, $anneEmbed)
    {

        try {
            if (!$data) {
                return ['success' => false, 'message' => "FUCK YOU I WON'T DO WHAT YOU TELL ME"];
            }
//            $pinecone = new PineconeCore();

            $jobData = UpsertToPineconeJob::dispatch($vector, $data['id'], $discordUserId, $anneEmbed);



            return [
                'success' => true,
//                'data' => $pinecone->upsert(vector: $vector, id: $data['id'], discordUserId: $discordUserId, anneEmbed: $anneEmbed)
                'data' => $jobData,
            ];

        } catch (\Exception $e) {
            Log::debug($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());

            return [
                'success' => false,
                'data' => $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile(),
            ];
        }
    }

    /**
     * @param $message
     * @return array
     */
    protected function initQuery(Message $message, $useGpt=false): array
    {

        $gptPrompt = [];

        // if using ChatGPT3.5Turbo
        if($useGpt){
            $gptPrompt = $this->getGptPrompt($gptPrompt);
        }

        $prompt = CommonKnowledge::selfAwareness() . "\n\n";

        $prompt .= CommonKnowledge::temporalAwareness() . "\n\n";

        $prompt .= CommonKnowledge::basicInstructions() . "\n\n";

        $personName = $message->author->username;
        $personNameShown = $message->author->displayname;
        $personId = $message->author->id;


        $lastMessage = null;

        // get the person object from db
        $person = Person::firstOrCreate(['name' => $personName, 'id' => $personId]);

        // is it their first message? if not, let's add their stuff to the prompt
        if ($person->message_count > 0) {

//            $prompt = messageHistoryHandler::addMostRecentMessage($prompt, $person, $personNameShown);
            $prompt = messageHistoryHandler::addMostRecentMessage($prompt, $person, $personNameShown);

            if($useGpt){
                $gptHistory = messageHistoryHandler::addMostRecentMessageGPT($person, $personNameShown);
                foreach($gptHistory as $history){
                    $gptPrompt[] = $history;
                }
            }
        }

        return array($prompt, $person, $gptPrompt);
    }

    /**
     * @param $message
     * @return mixed
     * This is the output for the vector test list that you get from the test command. it does NOT affect the regular query response.
     */
    protected function vectorQueryReturnTest($message): mixed
    {
        $promptRemoveTag = substr($message->content, 6);

        //get vector for user's message
        $userEmbed = $this->buildEmbedding($promptRemoveTag)->embeddings[0]->embedding;

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

    private function addHistoryFromVectorQuery(array $resultArray, string $promptWithPreloads)
    {
//        $vectorPrompt = $promptWithPreloads .
         $vectorPrompt =   "";

        try {

            //only use vectors with score above the threshhold (hardcoded to .79 for now, this will eventually move to front end)
            foreach ($resultArray['matches'] as $result) {
        if(!$result){
            continue;
        }
               if ($result->score < 0.79) {
                    continue;
                }
                $isAnne = false;
                //if it's anne's message vector
                if (data_get($result, 'metadata.anne', false) !== false) {
                    $isAnne = true;
                    //get message id
                    $id = substr($result->id, 5);

                    $userMessage = new Messages;
                    if ($id) {
                        $priorMessageData = $userMessage->find('id');
                        if ($priorMessageData) {
                            $priorMessageData = $priorMessageData->toArray();
                        }
                    }
                    $priorMessageOutput = trim($priorMessageData['message']) ?? 'Could not load prior user message from anne message.';
                    $priorMessageUser = Person::find(trim($priorMessageData['user_id']))->name ?? null;
                    //grab anne message model, query it for that message id, pull result
                    $anneMessageModel = new AnneMessages;
                    $messageData = $anneMessageModel->where('input_id', $id)->first() ?? null;

                    if ($messageData) {
                        $messageData = $messageData->toArray() ?? [];
                    } else {
                        Log::debug('Missing messageData on id: ' . $id);
                        continue;
                    }

                    //trim the message and put 'you' as the user (because anne said it and its a prompt to her)
                    $messageOutput = trim($messageData['message']) ?? 'Could not load message.';
                    $user = "You";

                    //Output is like: [HH:MM:SS MM/DD/YY] Username: message, and for anne messages we include the message that it is a reply to
                    $vectorPrompt .= "\n" .
                        $date = '[' . Carbon::parse($priorMessageData['created_at'])->toDateTimeString() . '] ' .  $priorMessageUser . ": '" . $priorMessageOutput;
                    $vectorPrompt .= "\n" .
                        $date = '[' . $result->metadata->dateTime . '] ' .  $user . ": '" . $messageOutput;

                    //if its a user's message vector...
                } else {
                    $id = $result->id;
                    $messageModel = new Messages;
                    $messageData = $messageModel->with('anneReply')->where('id', $id)->first();
                    if (!$messageData) {
                        Log::debug('Missing messageData on id: ' . $id);
                        continue;
                    }
                    //same shit BUT we need to grab anne's response too, which I'm pretty sure I set up a relationship for
                    $anneReplyMessage = $messageData->anneReply ?? null;

                    Log::debug(json_encode($anneReplyMessage, 128));

                    $messageOutput = $messageData['message'] ?? 'Could not load message.';
                    $people = new Person;
                    $user = $people->where('id', $messageData['user_id'])->first()->name ?? '??';

                    //same shit as anne's but the inverse, we include anne's message after the fact
                    $vectorPrompt .= "\n" .
                        $date = '[' . $result->metadata->dateTime . '] ' .  $user . ": '" . $messageOutput;
                    if($anneReplyMessage) {
                        $vectorPrompt .= "\n" .
                            $date = '[' . Carbon::parse(data_get($anneReplyMessage, 'created_at', null)->toDateTimeString()) . '] ' . 'You: ' . trim($anneReplyMessage->message);
                    }else{
                        Log::debug('Missing Anne response for messageId #'. $id . ". Expected: " . (string)$messageData->anneReply->id ?? '[!missing!]');
                    }
                    }


            }

        } catch (\Exception $e) {
            //handle errors, send to log (eventually on frontend)
            Log::debug($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            Log::debug("Vector prompt preload error.");
        }
        //get summary from other model
        if($vectorPrompt) {
            $summarized = $this->summarizeVectorResult($vectorPrompt);
        }else{
            $summarized = "No relevant messages found.";
        }
        //Take the pre-prompt, which already has the user input, add some instructions for this, attach summarized chat history, return to main function
        $result = $promptWithPreloads .
            "Here is a summary of related conversations from your memory.\n
            Use it as a reference for your response.\n
            If you think part of this summary is not relevant to this conversation, you can ignore it.\n
            You are not required to include the date, and should not unless it is important.\n
            \n\nSummary:\n"
            . $summarized;

        return $summarized;
    }


    //This is for GPT, we tried this and it wasn't great but I'm leaving the methods just in case we wanna use em
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



    protected function analyzeUserInput(string $input, string $user)
    {
        return (new analyzeUserInput())->basic($input, $user);
    }

    //This is the function that summarizes the chat history that we retrieved from the vector query. TODO: improve this prompt
    private function summarizeVectorResult(string $vectorPrompt)
    {

        $summaryPrompt = "You are a chatbot AI. Your current task is to summarize your chat history. You will be given a list of messages with timestamps.
        You are to output a summary of the messages, in a way that is optimized for a separate prompt that will be given to you later,
        which you can reference as your long-term memory.\n\n".
            "If a message starts with 'You: ' then you said it. If it starts with another name, then that's the user who said it.\n\n".
            "If the message refers to 'anne', that means its referring to you.\n\n"
            . "-----\n\n"
            ."Example Input:\n\n"
            ."[2023-03-01 12:29:48] ambi: What do you think of gils?\n\n
            [2023-03-01 12:29:50] You: I think gils is great!\n\n
            [2023-03-14 11:29:48] gils: What do I think of gils?\n\n
            [2023-03-14 11:29:50] You: Yes, of yourself.\n\n
            ----- \n\n
            Example Summary:
            Ambi asked you what you thought of gils and you said you thought gils was great on March 1st.\n
            Gils asked you what she thought of gils and you said yes, of herself on March 14th.\n\n
            ----- \n\n"

            ."Input:\n\n" . $vectorPrompt . "\n\n ----- \n\n
            Summary: \n\n";

        $result = OpenAI::completions()->create(['model' => 'text-davinci-003',
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
     * @param array $gptPrompt
     * @return array
     */
    protected function getGptPrompt(array $gptPrompt): array
    {
        $gptPrompt[] = ['role' => 'system',
            'content' => 'Your name is Anne. You are a member of a discord community, and you are speaking with other members of the community.\n
            Interact with the users, answer their questions, and use the provided information to help define who you are and provided past messages to help you
            recall past conversations you have had.'];

        $gptPrompt[] = [
            'role' => 'user',
            'content' => CommonKnowledge::selfAwareness(),
        ];

        $gptPrompt[] = [
            'role' => 'system',
            'content' => CommonKnowledge::temporalAwareness(),
        ];

        $gptPrompt[] = [
            'role' => 'user',
            'content' => CommonKnowledge::basicInstructions(),
        ];
        return $gptPrompt;
    }
}
