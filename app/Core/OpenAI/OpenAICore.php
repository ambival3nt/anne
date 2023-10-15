<?php

namespace App\Core\OpenAI;

use App\Core\config\CommonKnowledge;
use App\Core\Memory\messageHistoryHandler;
use App\Core\OpenAI\Prompts\analyzeUserInput;
use App\Core\VectorDB\PineconeCore;
use App\Core\VectorDB\VectorQueryReturn;
use App\Core\YouTube\VideoQuery;
use App\Jobs\UpsertToPineconeJob;
use App\Models\Anne;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use App\Models\ThoughtSummary;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use OpenAI;

class OpenAICore
{
    //This is the main query function for the bot, if it's going to be using OpenAI's API
//    private analyzeUserInput $analyzeUserInputFormatted;
    private VectorQueryReturn $vectorQueryReturn;

    public function __construct()
    {
        // this is for her busted tool selection stuff
//        $this->analyzeUserInputFormatted = new analyzeUserInput();

        // this gets her vectors and makes them useful
        $this->vectorQueryReturn = new VectorQueryReturn($this);
    }

    public function query($message, $discord, $mention = null, $reply = null, $lastMessage)
    {

        $client = OpenAI::client(getenv('OPENAI_API_KEY'));

        $promptRemoveTag = null;

        // Triggers for anne to respond
        if ($mention) {
            $promptRemoveTag = str_replace('@' . $mention->id, 'anne', $message->content);
            //init query
            list(
                $prompt,
                $person,

                ) = $this->initQuery($message, $discord);


        } elseif (str_starts_with(strtolower($message->content), "anne,") && !$message->author->bot) {
            //init query
            list(
                $prompt,
                $person,

                ) = $this->initQuery($message, $discord);


            //chop tag off string
            $promptRemoveTag = substr($message->content, 5);
        }
        if ($promptRemoveTag) {

            // AnneActions::checkForAction($promptRemoveTag, $message);

            try {

                //get vector for user's message
                $userEmbed = $this->buildEmbedding($promptRemoveTag, $client)->embeddings[0]->embedding;

                //add any preload prompts etc
                $promptWithPreloads = $prompt . $promptRemoveTag;

                // Query pinecone with the user embedding
                $vectorQueryResult = new PineconeCore;
                $resultArray = $vectorQueryResult->query($userEmbed);

                //parse vectors into prompt
                $vectorQuery = VectorQueryReturn::addHistoryFromVectorQuery($resultArray, $message, $client, $this) ?? "";

                //prompt with history summary attached
                $promptWithVectors = $vectorQuery['result'];

                //summary alone for db later
                $summary = $vectorQuery['summary'];

                $userName = $message->author->displayname;

                $promptWithPreloads .= "-----\n" . $promptWithVectors;
                $promptWithPreloads .= "\n $userName: $promptRemoveTag\n\n



                Anne: ";

                $functionCaller = '';
                $functionCaller = $this->getFunctions($promptRemoveTag, $client) ?? [];




                if (data_get($functionCaller, 'message', false) !== false) {
//                    $message->channel->sendMessage('i hit the thing ffs');
                    return $message->channel->sendMessage($functionCaller);
                }

                //get OpenAI response

                $result = $client->completions()->create([
                        'model' => 'gpt-3.5-turbo-instruct',
                        'prompt' => $promptWithPreloads,
                        'temperature' => 0.9,
                        'max_tokens' => 700,
                        'stop' => [
                            '-----',
                        ],
//                        'frequency_penalty' => 1.2,
//                        'presence_penalty' => 1.2,
                        'best_of' => 3,
                        'n' => 3,
                    ]
                );

                $responsePath = $result['choices'][0]['text'];

                //update person model

               $person->update([
                    'last_message' => $promptRemoveTag,
                    'last_response' => $responsePath,
                    'message_count' => $person->message_count + 1,
                    'avatar' => $message->author->avatar,
                ]);


                //init message model
                $messageModel = new Messages();

                //build anne's embedding for pinecone
                $anneEmbed = $this->buildEmbedding($responsePath, $client)->embeddings[0]->embedding;

                $messageModel = $messageModel->create([
                    'user_id' => (string)$person->id,
                    'message' => $promptRemoveTag,
                    'response' => $responsePath
                ]);


                //Get cosine similarity locally (for testing, maybe permanent)
//                $cosineSimilarity = Distance::cosineSimilarity($userEmbed, $anneEmbed);

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


                // Save anne's summarized version of the history
                $thoughtModel = new ThoughtSummary();

                $thoughtModel = $thoughtModel->create([
                    'message_id' => $messageModel->id,
                    'response_id' => $anneMessage->id,
                    'summary' => $summary,
                ]);

            } catch (\Exception $e) {
                Log::channel('db')->debug($e->getMessage());
                return $message->reply($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . ". Guess you better get on that.");
                //    return $message->reply('For some reason I cannot explain, I do not have an answer.');
            }

            //todo: if this debug shit all works move it to its own class to be invoked anywhere we need it
            // if(Anne::all()->first()->debug){
//                $responsePath .= "\nCosine Similarity: " . $cosineSimilarity;
//            }

            return $message->reply($responsePath);
        } elseif ((str_starts_with(strtolower($message->content), "anne show me ")) && !$message->author->bot) {

            $result = $client->images()->create([
                'prompt' => substr($message->content, 12),
                'n' => 1,
                'size' => "1024x1024"
            ]);


            $image = file_get_contents($result['data'][0]['url']);

            $filename = 'discordUpload' . Carbon::now()->toDateTimeString() . '.png';
            $put = file_put_contents($filename, $image);

            $builder = MessageBuilder::new();

            $builder->addFile($filename);

            return $message->reply($builder);
        } else {
            return "Something went wrong.";
        }

    }

    public function buildEmbedding($message, $client=null)
    {

        $embedString = $message;

        $embedVector = $client->embeddings()->create([
            'input' => $embedString,
            'model' => 'text-embedding-ada-002',
        ]);


        return $embedVector;

    }

    public function sendToPineconeAPI($vector, $data = null, $discordUserId, $anneEmbed)
    {

        try {
            if (!$data) {
                return ['success' => false, 'message' => "No data found. My bad."];
            }
            $jobData = UpsertToPineconeJob::dispatch($vector, $data['id'], $discordUserId, $anneEmbed);


            return [
                'success' => true,
                'data' => $jobData,
            ];

        } catch (\Exception $e) {
            Log::channel('db')->debug($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());

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
    protected function initQuery(Message $message, Discord $discord): array|string
    {
        //init prompt
        $prompt = CommonKnowledge::selfAwareness() . "\n\n"
                . CommonKnowledge::temporalAwareness() . "\n\n"
                . CommonKnowledge::basicInstructions() . "\n\n";

        //identify user and validate/save details
        list($personName, $personId, $personNameShown) = $this->getUserDiscordNamesAndId($message);

        $this->saveUserAndMessageToAnneModel($personName, $message);
        $person = $this->getOrCreateUserPersonModel($personId, $personName, $message);
        $this->saveUserAvatarToDb($person, $message);
        $userAliasList = $this->handleUserAliases($person, $personNameShown, $personId, $personName);
        $historyArray = messageHistoryHandler::addMostRecentMessage($prompt, $person, $personNameShown, $message, $userAliasList) ?? [];

        return array($historyArray['prompt'], $person);
    }

    /**
     * @param $message
     * @return mixed
     * This is the output for the vector test list that you get from the test command. No effect on regular response.
     */
    protected function vectorQueryReturnTest($message, $client=null): mixed
    {
        return $this->vectorQueryReturn->vectorQueryReturnTest($message, $client);
    }


    protected function analyzeUserInput(string $input, string $user, $client=null)
    {
        return (new analyzeUserInput())->basic($input, $user, $client);
    }

    //This is the function that summarizes the chat history that we retrieved from the vector query. TODO: improve this prompt

//    public function webSearch($query)
//    {
//
//        $google = new Search();
//        $results = $google->getSearchResults($query);
//
//        dd($results);
//
//        $resultArray = [];
//        if ($results) {
//
//        } else {
//            echo "No results found.";
//        }
//
//        return $response->getBody()->getContents();
//    }



    public function getFunctions($incomingThing, $client)
    {

        $system = 'You are a function router. Your job is to determine if the user needs to use a function, and what that function is, if so. You are not required to return any function, if none are relevant.
        If a function is required, run it and return the result. If no function is required, return nothing.';

        $functions = [
//            [
//                "name" => "web_search",
//                "description" => "The user is asking for something that requires a web search.",
//                "parameters" => [
//                    "type" => "object",
//                    "properties" => [
//                        "query" => [
//                            "type" => "string",
//                            "description" => "The search query, or what the user is asking for.",
//                        ],
//                        "search_result" => [
//                            "type" => "string",
//                            "description" => "The result of the search, if any.",
//                        ],
//                        ],
//                    ],
//                ],
            [
                'name' => 'youtube_search',
                'description' => 'The user is asking for something that requires a youtube search.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'The search query, or what the user is asking for.',
                        ],
                        'search_result' => [
                            'type' => 'string',
                            'description' => 'The result of the search, if any.',
                        ],
                    ],
                ],
            ],
        ];


$output = null;
        $messages = [
            [
                'role' => 'system',
                'content' => $system,
            ],
            [
                'role' => 'user',
                'content' => $incomingThing,
            ],
        ];

            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo-0613',
                'messages' => $messages,
                'temperature'=> 0.5,
                'top_p' => 0.5,
                'max_tokens' => 700,
                'functions' => $functions,
                'function_call' => 'auto',
            ]);

            $resMessage = $response['choices'][0]['message'];

            if(data_get($resMessage, 'function_call', false) !== false){

                $availableFunctions = [
//                    "web_search" => 'webSearch',
                    'youtube_search' => 'youtubeSearch',
                ];

            $functionName = $resMessage['function_call']['name'];
            $functionToCall = $availableFunctions[$functionName];
            $functionArgs = json_decode($resMessage['function_call']['arguments']);
            $functionResponse = $this->$functionToCall($functionArgs);
            } else {
                $functionResponse = null;
            }

            if($functionResponse) {
                $messages[] = [
                    'role' => 'function',
                    'name' => $functionName,
                    'content' => $functionResponse,
                ];
                $response = $client->chat()->create([
                    'model' => 'gpt-3.5-turbo-0613',
                    'messages' => $messages,
                    'temperature' => 0.5,
                    'top_p' => 0.5,
                    'max_tokens' => 700
                ]);
                $output = $response['choices'][0]['message']['content'] ?? null;
            }

    if($output){
        return $output;
    }
        $outie = [];


        foreach ($response as $result) {
            Log::debug(json_encode($result));
            $outie[] = $result;

        }

        return $outie;
    }

    public function youtubeSearch($query)
    {
        $youtube = new VideoQuery();
        $results = $youtube->search($query->query);
        return $results;
    }

    /**
     * @param $person
     * @param array|bool|string|null $personNameShown
     * @param string $personId
     * @param string $personName
     * @return array|false|mixed[]|string|string[]|null
     */
    protected function handleUserAliases($person, array|bool|string|null $personNameShown, string $personId, string $personName): string|array|null|false
    {
        try {
            if ($personNameShown !== $person->name) {
                //add to alias list
                $person->nameMapping($personNameShown, $personId, $personName);
            }
        } catch (\Exception $e) {
            Log::channel('db')->debug($e->getMessage() . ' on line ' . $e->getLine());
        }

        $userAliasList = $person->getNameList() ?? [];
        $userAliasList = mb_convert_encoding($userAliasList, 'UTF-8', 'UTF-8');
        return $userAliasList;
    }

    /**
     * @param $person
     * @param Message $message
     * @return void
     */
    protected function saveUserAvatarToDb($person, Message $message): void
    {
        if (!$person->avatar) {
            $person->avatar = $message->author->avatar ?? null;
            $person->save();
        }
    }

    /**
     * @param string $personId
     * @param string $personName
     * @param Message $message
     * @return Person | array
     */
    protected function getOrCreateUserPersonModel(string $personId, string $personName, Message $message): Person | array
    {
// get the person object from db

        //this should fix the username change bug
        $person = Person::updateOrCreate(
            ['id' => $personId],
            ['name' => $personName]
        ) ?? null;

        $person = Person::find($personId) ?? null;

        //create new person if they don't exist
        if (!$person) {
            $person = Person::create([
                'id' => $personId,
                'name' => $personName,
                'avatar' => $message->author->avatar ?? null,
                'last_message' => 'Hello Anne I am ' . $personName . " and we haven't met before.",
                'last_response' => 'Hello ' . $personName . " I am Anne.",
            ]);
        }
        if($person) {
            return $person;
        }else{
            return [];
        }
    }

    /**
     * @param string $personName
     * @param Message $message
     * @return void
     */
    protected function saveUserAndMessageToAnneModel(string $personName, Message $message): void
    {
        try {
            $anneModel = Anne::all()->first();
            $anneModel->last_user = $personName;
            $anneModel->last_message = $message->content ?? '';
            $anneModel->save();
        } catch (\Exception $e) {
            Log::channel('db')->debug($e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    /**
     * @param Message $message
     * @return array
     */
    protected function getUserDiscordNamesAndId(Message $message): array
    {
        $personName = $message->author->username;
        $attribs = $message->getRawAttributes();
        $globalName = $attribs['author']->global_name ?? null;
        $personId = $message->author->id;
//        $personId = 402474631992180738;


        if (data_get($message->member, 'nick', null)) {
            $personNameShown = $message->member->nick;
        } elseif ($globalName) {
            $personNameShown = $globalName;
        } else {
            $personNameShown = $personName;
        }
        $personNameShown = mb_convert_encoding($personNameShown, 'UTF-8', 'UTF-8');
        return array($personName, $personId, $personNameShown);
    }
}
