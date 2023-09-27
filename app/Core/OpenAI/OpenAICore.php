<?php

namespace App\Core\OpenAI;

use App\Core\config\CommonKnowledge;
use App\Core\Memory\messageHistoryHandler;
use App\Core\OpenAI\Prompts\analyzeUserInput;
use App\Core\OpenAI\Prompts\ZiggyBasilisk;
use App\Core\VectorDB\PineconeCore;
use App\Core\VectorDB\VectorQueryReturn;
use App\Enums\AnneActions;
use App\Jobs\UpsertToPineconeJob;
use App\Models\Anne;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\PeopleNameMapping;
use App\Models\Person;
use App\Models\ThoughtSummary;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
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
//    private analyzeUserInput $analyzeUserInputFormatted;
    private VectorQueryReturn $vectorQueryReturn;

    public function __construct()
    {
        // this is for her busted tool selection stuff
//        $this->analyzeUserInputFormatted = new analyzeUserInput();

        // this gets her vectors and makes them useful
        $this->vectorQueryReturn = new VectorQueryReturn($this);
    }

    public function query($message, $discord, $mention = null, $reply=null, $lastMessage)
    {

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
                $userEmbed = $this->buildEmbedding($promptRemoveTag)->embeddings[0]->embedding;

                //add any preload prompts etc
                $promptWithPreloads = $prompt . $promptRemoveTag;

                // Query pinecone with the user embedding
                $vectorQueryResult = new PineconeCore;
                $resultArray = $vectorQueryResult->query($userEmbed);

                //parse vectors into prompt
                $vectorQuery = $this->addHistoryFromVectorQuery($resultArray, $message) ?? "";

                //prompt with history summary attached
                $promptWithVectors = $vectorQuery['result'];

                //summary alone for db later
                $summary = $vectorQuery['summary'];

                $userName= $message->author->displayname;

                $promptWithPreloads .= "-----\n" . $promptWithVectors;
                $promptWithPreloads .= "\n User: $promptRemoveTag\n\n



                Anne: ";

//                $functionCaller = $this->getFunctions($promptRemoveTag);

//                $message->channel->sendMessage(json_encode($functionCaller,128));
//                Log::debug(json_encode($functionCaller,128));
                //get davinci response

                    $result = OpenAI::completions()->create([
//                        'model' => 'text-davinci-003',
                        'model' => 'gpt-3.5-turbo-instruct',
                            'prompt' => $promptWithPreloads,
//                        'top_p' => .25,
                            'temperature' => 1,
                            'max_tokens' => 700,
                            'stop' => [
                                '-----',
                            ],
                            'frequency_penalty' => 1.2,
                            'presence_penalty' => 1.2,
                            'best_of' => 2,
                            'n' => 1,
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
                $anneEmbed = $this->buildEmbedding($responsePath, true)->embeddings[0]->embedding;

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
        }elseif ((str_starts_with(strtolower($message->content), "anne show me ") ) && !$message->author->bot) {

            $result = OpenAI::images()->create([
                'prompt' => substr($message->content, 12),
                'n' => 1,
                'size' => "1024x1024"
            ]);


           $image = file_get_contents($result['data'][0]['url']);

           $filename = 'discordUpload' . Carbon::now()->toDateTimeString().'.png';
           $put = file_put_contents($filename, $image);

           $builder = MessageBuilder::new();

            $builder->addFile($filename);

            return $message->reply($builder);
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
    protected function initQuery(Message $message, Discord $discord): array | string
    {
        $brainWindowArray = [];

        $prompt = CommonKnowledge::selfAwareness() . "\n\n";

        $prompt .= CommonKnowledge::temporalAwareness() . "\n\n";

        $prompt .= CommonKnowledge::basicInstructions() . "\n\n";

        $personName = $message->author->username;
        $attribs = $message->getRawAttributes();
        $globalName = $attribs['author']->global_name ?? null;
        $personId = $message->author->id;
//        $personId = 402474631992180738;



        if(data_get($message->member, 'nick', null)){
            $personNameShown = $message->member->nick;
        }elseif($globalName){
            $personNameShown = $globalName;
        }else{
            $personNameShown = $personName;
        }
        $personNameShown = mb_convert_encoding($personNameShown, 'UTF-8', 'UTF-8');


try{
        $anneModel = Anne::all()->first();
        $anneModel->last_user = $personName;
        $anneModel->last_message = $message->content ?? '';
        $anneModel->save();
}catch(\Exception $e){
    Log::channel('db')->debug($e->getMessage() . ' on line ' . $e->getLine());
}

   $lastMessage = null;

        // get the person object from db

        //this should fix the username change bug
        $person = Person::updateOrCreate(
            ['id' => $personId],
            ['name' => $personName]
        ) ?? null;

        $person = Person::find($personId) ?? null;

        if(!$person){
           $person = Person::create([
                'id' => $personId,
                'name' => $personName,
                'avatar' => $message->author->avatar ?? null,
                'last_message' => 'Hello Anne I am ' . $personName . " and we haven't met before.",
                'last_response' => 'Hello ' . $personName . " I am Anne.",
            ]);
        }


        if(!$person->avatar){
            $person->avatar = $message->author->avatar ?? null;
            $person->save();
        }

        try {
            if ($personNameShown !== $person->name) {

                $person->nameMapping($personNameShown, $personId, $personName);
            }
        }catch(\Exception $e){
            Log::channel('db')->debug($e->getMessage() . ' on line ' . $e->getLine());
        }

        $userAliasList = $person->getNameList() ?? [];


        $userAliasList = mb_convert_encoding($userAliasList, 'UTF-8', 'UTF-8');



        // is it their first message? if not, let's add their stuff to the prompt
       $historyArray = [];

       $historyArray = messageHistoryHandler::addMostRecentMessage($prompt, $person, $personNameShown, $message, $userAliasList) ?? [];

        return array($historyArray['prompt'], $person);
    }

    /**
     * @param $message
     * @return mixed
     * This is the output for the vector test list that you get from the test command. No effect on regular response.
     */
    protected function vectorQueryReturnTest($message): mixed
    {
        return $this->vectorQueryReturn->vectorQueryReturnTest($message);
    }

    private function addHistoryFromVectorQuery(array $resultArray, $message)
    {

         $vectorPrompt =   "";
        $priorMessageData = [];
        $priorMessageUser = '';
        $priorMessageOutput = '';
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
                    try{

                    if ($id) {
                        $priorMessageData = Messages::find($id) ?? null;
                        if ($priorMessageData) {
                            $priorMessageData = $priorMessageData->toArray() ?? [];
                            $priorMessageOutput = trim($priorMessageData['message']) ?? 'Could not load prior user message from anne message.';
                            $priorMessageUser = Person::find(trim($priorMessageData['user_id']))->name ?? '';
                        }else{

                            $priorMessageData = [];
                            $priorMessageUser = '';
                            $priorMessageOutput = '';
                        }
                    }
                    }catch(\Exception $e){
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

                    if(data_get($priorMessageData,'created_at',false) !== false){
                        $datestamp = Carbon::parse($priorMessageData['created_at'])->toDateTimeString();
                    }else{
                        $datestamp = "Date Unknown";
                    }

                    $vectorPrompt .= "\n" .
                        $date = '['.$datestamp.'] ' .  $priorMessageUser . ": '" . $priorMessageOutput;
                    $vectorPrompt .= "\n" .
                        $date = '[' . $result->metadata->dateTime . '] ' .  $user . ": '" . $messageOutput;

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

                    }catch(\Exception $e) {
                        Log::channel('db')->debug($e->getMessage() . ' on ' . $e->getLine());
                        $message->channel->sendMessage("I'm gonna be... BLUUUEUEUEUEUGUEGHGHGHHrrrhghgh\n" . json_encode($result, 128));
                    }
                    if($anneReplyMessage) {
                        $vectorPrompt .= "\n" .
                            $date = '[' . Carbon::parse(data_get($anneReplyMessage, 'created_at', null)->toDateTimeString()) . '] ' . 'You: ' . trim($anneReplyMessage->message);
                    }else{
                        Log::channel('db')->debug('Missing Anne response for messageId #'. $id . ". Expected: " . (string)$messageData->anneReply->id ?? '[!missing!]');
                    }
                    }


            }

        } catch (\Exception $e) {
            //handle errors, send to log (eventually on frontend)
            Log::channel('db')->debug($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            Log::channel('db')->debug("Vector prompt preload error.");
        }
        //get summary from other model
        $summarized = $this->summarizeVectorResult($vectorPrompt, $message);

        //Take the pre-prompt, which already has the user input, add some instructions for this, attach summarized chat history, return to main function
        $result = "Here is a summary of related conversations from your memory.\n
            Use it or parts of it as a reference for your response, if it's relevant. Otherwise, you can ignore.\n

            \n\nSummary:\n"
            . $summarized;

        return [
            'result'=>$result,
            'summary'=>$summarized,
            ];
    }


    protected function analyzeUserInput(string $input, string $user)
    {
        return (new analyzeUserInput())->basic($input, $user);
    }

    //This is the function that summarizes the chat history that we retrieved from the vector query. TODO: improve this prompt
    private function summarizeVectorResult(string $vectorPrompt, $message)
    {

        $userInput = $message->content ?? '';
        $mention = $message->mentions->first() ?? null;

        if ($mention) {
            $userInput = str_replace('@' . $mention->id, 'anne', $message->content);
        } elseif (str_starts_with(strtolower($message->content), "anne,") && !$message->author->bot) {
            $userInput = substr($message->content, 5);
        }

        $summaryPrompt = "You are a chat summarization AI. You will be given a list of messages with timestamps.
       You are to output a summary of the relevant messages,  optimized as a prompt to give to a LLM named Anne. Carefully read all of the input before summarizing.\n\n
        It is important that your summary be as concise, and short as possible, while still being accurate. Use the supplied current user input as reference for your summary, and discard any history that seems unrelated.\n\n

IMPORTANT: Anne is a fictional character. A human. But she never breaks character.\n
IMPORTANT: The summary is for Anne. The summary should be a prompt for Anne to use to respond.\n
IMPORTANT: Try to keep it in chronological order.\n

       IMPORTANT: Here is the current user input: \n\n

       $userInput\n\n
         Here is the chat history you are summarizing:\n\n
        $vectorPrompt\n\n
        If a message starts with 'You: ' then the bot said it. Anything else, that's the user who said it.\n\n" .
        "If the message refers to 'anne', that means its referring to the LLM you are summarizing or.\n\n" .
            "When you are finished go over your summary again and see if it can be improved."
        . "Summary: \n\n";

        $result = OpenAI::completions()->create([
                'model' => 'gpt-3.5-turbo-instruct',
                'prompt' => $summaryPrompt,
                'max_tokens' => 600,
                'stop' => [
                    '-----',
                ],
                'n' => 1,
            ]
        );

//        $result = OpenAI::chat()->create([
//                'model' => 'gpt-3.5-turbo',
//
//                'messages' => [
//                    [
//                        "role" => "system",
//                        "content" => $summaryPrompt,
//                    ],
//                    ],
//                    'temperature' => .1,
//                    'max_tokens' => 600,
//                    'stop' => [
//                        '-----',
//                    ],
//                    'frequency_penalty' => 1.2,
//                    'presence_penalty' => 1.2,
//                    'n' => 1,
//                ]
//
//        );

    $return = $result['choices'][0]['text'];
//Log::channel('db')->debug($result['choices'][0]['message']['content']);
//        $return = $result['choices'][0]['message']['content'];

    return $return;

    }

    public function getFunctions($prompt)
    {

        $system = "Your job is to run the proper function based on the user's request.\n";

        $functions = [
            [
                "name" => "do_web_search",
                "description" => "Do a web search for the requested query",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                    ],
                    "required" => ["query"],
                ],
            ]
        ];
//
//        'functions' => [
//        [
//            'name' => 'get_current_weather',
//            'description' => 'Get the current weather in a given location',
//            'parameters' => [
//                'type' => 'object',
//                'properties' => [
//                    'location' => [
//                        'type' => 'string',
//                        'description' => 'The city and state, e.g. San Francisco, CA',
//                    ],
//                    'unit' => [
//                        'type' => 'string',
//                        'enum' => ['celsius', 'fahrenheit']
//                    ],
//                ],
//                'required' => ['location'],
//            ],
//        ]
//    ]



        $prompto = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo-0613',
                'messages' => [

                    [
                        "role" => "system",
                        "content" => $system,
                    ],
                    [
                        "role" => "user",
                        "content" => $prompt,
                    ],
                    [
                        "role"=>"assistant",
                        "content"=>"What function should I call?"
                    ]

                ],
                'temperature' => .1,
                'max_tokens' => 600,
                'stop' => [
                    '-----',
                ],
                'functions'=>$functions,
                'frequency_penalty' => 1.2,
                'presence_penalty' => 1.2,
                'n' => 1,
            ]
        );
$outie = [];
    foreach($prompto as $result){
        Log::debug(json_encode($result));
        $outie[]= $result->message->functionCall->name ?? null;
    }

//        if response_message.get("function_call"):
//            # Step 3: call the function
//            # Note: the JSON response may not always be valid; be sure to handle errors
//            available_functions = {
//            "get_current_weather": get_current_weather,
//        }  # only one function in this example, but you can have multiple
//        function_name = response_message["function_call"]["name"]
//        fuction_to_call = available_functions[function_name]
//        function_args = json.loads(response_message["function_call"]["arguments"])
//        function_response = fuction_to_call(
//            location=function_args.get("location"),
//            unit=function_args.get("unit"),
//        )

return $outie;
    }

    public function webSearch($query){
        Log::debug('I work!');
        return 'I work!';
    }
}
