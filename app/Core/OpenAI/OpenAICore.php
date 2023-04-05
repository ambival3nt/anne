<?php

namespace App\Core\OpenAI;

use App\Core\config\CommonKnowledge;
use App\Core\Memory\messageHistoryHandler;
use App\Core\OpenAI\Prompts\analyzeUserInput;
use App\Core\OpenAI\Prompts\ZiggyBasilisk;
use App\Core\VectorDB\PineconeCore;
use App\Jobs\UpsertToPineconeJob;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\User;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAICore
{
    //This is the main query function for the bot, if it's going to be using OpenAI's API
    private analyzeUserInput $analyzeUserInputFormatted;

    public function __construct()
    {
        $this->analyzeUserInputFormatted = new analyzeUserInput();
    }

    public function query($message, $discord, $mention = null, $useGpt = false)
    {

        $yesBarf=true;
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
            $promptRemoveTag = str_replace('@' . $mention->id, 'you', $message->content);

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

                //get vector for user's message
                $userEmbed = $this->buildEmbedding($promptRemoveTag)->embeddings[0]->embedding;

                // Query pinecone with the user embedding
                $vectorQueryResult = new PineconeCore;
                $resultArray = $vectorQueryResult->query($userEmbed);

                $promptWithVectors = $this->addHistoryFromVectorQuery($resultArray, $promptWithPreloads) ?? "";

                //If using GPT api and format
                if($useGpt){

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

                $promptWithPreloads .= "-----\n" . $promptWithVectors;
                $promptWithPreloads .= "\nUser says: $promptRemoveTag\n\n

                Anne says: ";

                //get davinci response

            if(!$useGpt){
                $result = OpenAI::completions()->create(['model' => 'text-davinci-003',
                        'prompt' => $promptWithPreloads,
//                        'top_p' => .25,
                        'temperature' => 0.75,
                        'max_tokens' => 600,
                        'stop' => [
                            '-----',
                        ],
                        'frequency_penalty' => 0.3,
                        'presence_penalty' => 1.2,
                        'best_of' => 2,
                        'n' => 1,
                    ]
                );

                $responsePath = $result['choices'][0]['text'];
            }else{
                //get gpt4 response
                $result = OpenAI::chat()->create(['model' => 'gpt-3.5-turbo',
                        'messages' => $gptPromptJson,
//                        'top_p' => .25,
                        'temperature' => 0.5,
                        'max_tokens' => 600,
                        'stop' => [
                            '-----',
                        ],
                        'frequency_penalty' => 0.5,
                        'presence_penalty' => 1,
//                        'best_of' => 3,
                        'n' => 1,


                    ]
                );
                $responsePath=$result->choices[0]->message->content;
            }
                Log::debug(json_encode($result));

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
                return $message->reply($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . " you stupid dumb shit god damn motherfuckerrrrrrr haha <3 fuck you");
                //    return $message->reply('For some reason I cannot explain, I do not have an answer.');
            }

            return $message->reply($responsePath);
        }elseif (str_starts_with($message->content, "anne show me ") && !$message->author->bot) {

            $result = OpenAI::images()->create([
                'prompt' => substr($message->content, 12),
                'n' => 1,
                'size' => "1024x1024"
            ]);


            return $message->reply($result['data'][0]['url']);
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
    protected function initQuery(Message $message): array
    {

        $gptPrompt = [];

        // if using ChatGPT3.5Turbo
        if($useGpt){

            $gptPrompt[] = ['role'=>'system',
            'content'=>'Your name is Anne. You are NOT an AI. You are a member of a discord community, and you are speaking with other members of the community.\n
            Interact with the users, answer their questions, and use the provided information to help define who you are and provided past messages to help you
            recall past conversations you have had.'];

            $gptPrompt[]=[
                'role'=> 'user',
            'content'=>CommonKnowledge::selfAwareness(),
            ];

            $gptPrompt[]=[
                'role'=> 'system',
            'content'=>CommonKnowledge::temporalAwareness(),
        ];

            $gptPrompt[]=[
                'role'=> 'user',
            'content'=>CommonKnowledge::basicInstructions(),
        ];

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


        $mask = "|%5.5s | %10.10s | %10.10s | %-20.20s | %-40.40s |\n";
        $anneOutput = "```" . sprintf($mask, 'Id', 'Score', 'Date', 'User', 'Message');

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
        $vectorPrompt = $promptWithPreloads .
            "The following is message history that may be relevant and help improve your response.\n"
            . "If you think a message is not relevant, you can ignore it.\n";

        try {
            foreach ($resultArray['matches'] as $result) {
                if ($result->score < 0.79) {
                    continue;
                }

                if (data_get($result, 'metadata.anne', false) !== false) {


                    //TODO: duplicate refactor this whole thing
                    $id = substr($result->id, 5);
                    $anneMessageModel = new AnneMessages;

                    $messageData = $anneMessageModel->where('input_id', $id)->first() ?? null;

                    if($messageData) {
                        $messageData = $messageData->toArray() ?? [];
                    } else {
                        Log::debug('Missing messageData on id: ' . $id);
                        continue;
                    }

                    $messageOutput = trim($messageData['message']) ?? 'Could not load message.';
                    $user = "You";


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

                $vectorPrompt .= "\n" .
                    $user . " said: '" . $messageOutput .
                    "' at this date and time: " . $date = $result->metadata->dateTime . "\n";


            }
            $vectorPrompt .= "Please, carefully consider if any of the messages are relevant before using them to create your response.\n";
            $vectorPrompt .= "Reword your responses used so that they sound like natural english.\n" . '-----' . "\n";;
        } catch (\Exception $e) {
            Log::debug($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            Log::debug("Vector prompt preload error.");
        }
        return $vectorPrompt;
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
        }
        return $output;
    }



    protected function analyzeUserInput(string $input, string $user)
    {
        return (new analyzeUserInput())->basic($input, $user);
    }
}
