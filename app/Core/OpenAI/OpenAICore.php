<?php

namespace App\Core\OpenAI;

use App\Core\config\CommonKnowledge;
use App\Core\Memory\messageHistoryHandler;
use App\Core\OpenAI\Prompts\analyzeUserInput;
use App\Core\OpenAI\Prompts\ZiggyBasilisk;
use App\Core\VectorDB\PineconeCore;
use App\Jobs\UpsertToPineconeJob;
use App\Models\Anne;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\PeopleNameMapping;
use App\Models\Person;
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
    private analyzeUserInput $analyzeUserInputFormatted;

    public function __construct()
    {
        $this->analyzeUserInputFormatted = new analyzeUserInput();
    }

    public function query($message, $discord, $mention = null, $reply=null, $lastMessage)
    {




        $promptRemoveTag = null;


if($message->content === '-=spam'){

    $encodedArray = str_split(json_encode($message, 128), 2000);

   foreach($encodedArray as $item){
       $message->reply($item);
   }

}
//Test route for anne's brain///////////////////////////
        elseif (str_starts_with($message->content, "-=think") && !$message->author->bot) {
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
        elseif (str_starts_with($message->content, "-=test")
            && !$message->author->bot
        ) {

            return $this->vectorQueryReturnTest($message);



            ///////////////////////////////////////////////////////




            //is someone mentionining anne? (evidently this covers replies too)
        } elseif ($mention) {
            $promptRemoveTag = str_replace('@' . $mention->id, 'anne', $message->content);
            //init query
            list(
                $prompt,
                $person,
                $brainArray,
                ) = $this->initQuery($message, $discord);

            //standard query
        } elseif (str_starts_with(strtolower($message->content), "anne,") && !$message->author->bot) {
            //init query
            list(
                $prompt,
                $person,
                $brainArray,
                ) = $this->initQuery($message, $discord);

            $brainEmbed = $this->buildBrainWindowEmbed($brainArray, $discord);


            $brainBuilder = new MessageBuilder();
            $brainBuilder->addEmbed($brainEmbed);
//            $message->channel->createThread($brainBuilder, $message->author->username . ' ' . $message->author->discriminator . ' ' . Carbon::now()->toDateTimeString());
//            $message->channel->sendMessage($brainBuilder);


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

                $userName= $message->author->displayname;

                $promptWithPreloads .= "-----\n" . $promptWithVectors;
                $promptWithPreloads .= "\n User: $promptRemoveTag\n\n

                Anne: ";

                //get davinci response

                    $result = OpenAI::completions()->create(['model' => 'text-davinci-003',
                            'prompt' => $promptWithPreloads,
//                        'top_p' => .25,
                            'temperature' => 1,
                            'max_tokens' => 600,
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
                $cosineSimilarity = Distance::cosineSimilarity($userEmbed, $anneEmbed);

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
                return $message->reply($e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . ". Guess you better get on that.");
                //    return $message->reply('For some reason I cannot explain, I do not have an answer.');
            }

            //todo: if this debug shit all works move it to its own class to be invoked anywhere we need it
            if(Anne::all()->first()->debug){
                $responsePath .= "\nCosine Similarity: " . $cosineSimilarity;
            }

            return $message->reply($responsePath);
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
                return ['success' => false, 'message' => "No data found. My bad."];
            }
            $jobData = UpsertToPineconeJob::dispatch($vector, $data['id'], $discordUserId, $anneEmbed);



            return [
                'success' => true,
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



        if(data_get($message->author, 'nick', null)){
            $personNameShown = $message->author->nick;
        }elseif($globalName){
            $personNameShown = $globalName;
        }else{
            $personNameShown = $personName;
        }
//        Log::debug(json_encode($attribs, 128));

        $brainWindowArray = [
            'username'=>$personName,
            'global_name'=>$globalName ?? 'n/a',
            'person_id'=>$personId,
            'nick'=>$message->author->nick ?? 'n/a',
            'person_name_shown'=>$personNameShown,
            ];


try{
        $anneModel = Anne::all()->first();
        $anneModel->last_user = $personName;
        $anneModel->last_message = $message->content;
        $anneModel->save();
}catch(\Exception $e){
    Log::debug($e->getMessage() . ' on line ' . $e->getLine());
}

   $lastMessage = null;

        // get the person object from db

        //this should fix the username change bug
        $person = Person::updateOrCreate(
            ['id' => $personId],
            ['name' => $personName]
        );

        $person = Person::find($personId);

//if($message->author->id == getenv('OWNER_ID')){
//    $message->channel->sendMessage("PersonNameShown: $personNameShown, PersonId: $personId, person->name: $person->name");
//
////    $fuckinshit = json_encode($message->guild,128);
////    $fuckinshit = json_encode($message->channel->guild->members,128);
//    $fuckinshit = json_encode($message->getRawAttributes(),128);
//    for($i = 0; $i < strlen($fuckinshit); $i=$i+1990){
//        $message->channel->sendMessage("```json\n" . substr($fuckinshit,$i,1985) . "\n```");
//    }
//
//}

        try {
            if ($personNameShown !== $person->name) {

                $person->nameMapping($personNameShown, $personId, $personName);
            }
        }catch(\Exception $e){
            Log::debug($e->getMessage() . ' on line ' . $e->getLine());
        }

        $userAliasList = $person->getNameList();

        $brainWindowArray['aliasList'] = json_encode($userAliasList) ?? [];

        // is it their first message? if not, let's add their stuff to the prompt
       $historyArray = [];
        if ($person->message_count > 0) {


            $historyArray = messageHistoryHandler::addMostRecentMessage($prompt, $person, $personNameShown, $message, $userAliasList);
        }

        $brainWindowArray = array_merge($brainWindowArray, $historyArray['brain']);

        return array($historyArray['prompt'], $person, $brainWindowArray);
    }

    /**
     * @param $message
     * @return mixed
     * This is the output for the vector test list that you get from the test command. No effect on regular response.
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

         $vectorPrompt =   "";

        try {

            //only use vectors with score above the threshhold (hardcoded to .79 for now, this will eventually move to front end)
            foreach ($resultArray['matches'] as $result) {
                if ($result->score < 0.81) {
                    continue;
                }
                $isAnne = false;
                //if it's anne's message vector
                if (data_get($result, 'metadata.anne', false) !== false) {
                    $isAnne = true;
                    //get message id
                    $id = substr($result->id, 5);
try{
                    $userMessage = new Messages;
                    if ($id) {
                        $priorMessageData = $userMessage->find('id');
                        if ($priorMessageData) {
                            $priorMessageData = $priorMessageData->toArray();
                            $priorMessageOutput = trim($priorMessageData['message']) ?? 'Could not load prior user message from anne message.';
                            $priorMessageUser = Person::find(trim($priorMessageData['user_id']))->name ?? null;
                        }else{
                            $priorMessageData = [];
                            $priorMessageUser = '';
                            $priorMessageOutput = '';
                        }
                    }
}catch(\Exception $e){
    Log::debug($e->getMessage() . ' on line ' . $e->getLine());
}
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
        $summarized = $this->summarizeVectorResult($vectorPrompt);

        //Take the pre-prompt, which already has the user input, add some instructions for this, attach summarized chat history, return to main function
        $result = $promptWithPreloads .
            "Here is a summary of related conversations from your memory.\n
            Use it or parts of it as a reference for your response, if it's relevant. Otherwise, you can ignore.\n

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

        $summaryPrompt = "You are a summarization AI. Your current task is to summarize chat history. You will be given a list of messages with timestamps.
        You are to output a summary of the messages, in a way that is optimized as a prompt to give to a text-davinci-003 LLM. Carefully read all of the input before summarizing.\n\n

            If a message starts with 'You: ' then the bot said it. Anything else, that's the user who said it.\n\n".
            "If the message refers to 'anne', that means its referring to the text-davinci-003 LLM you are summarizing for.\n\n"
            . "-----\n\n"
            ."Input:\n\n"
            ."[2023-03-01 12:29:48] ambi: What do you think of cheese?\n\n
            [2023-03-01 12:29:50] You: I think cheese is great!\n\n
            [2023-03-14 11:29:48] gils: What do I think of cats?\n\n
            [2023-03-14 11:29:50] You: Yes, of cats.\n\n
            ----- \n\n
            Summary:
            Ambi asked you what you thought of cheese and you said you thought cheese was great on March 1st.\n
            Gils asked you what she thought of cats and you said yes, of cats on March 14th.\n\n
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

    private function buildBrainWindowEmbed(mixed $brainArray, $discord)
    {
        $embed = new Embed($discord);
        $embed->setTitle(("Anne's Brain"));
        $embed->setDescription("We're going to fucking get this figured out dammit");
        foreach($brainArray as $name=>$field) {
            $embed->addFieldValues(
                    $name,$field,false
                );
        }
        return $embed;
    }


}
