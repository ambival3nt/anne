<?php

namespace App\Models;

use App\Core\config\CommonKnowledge;
use App\Core\Memory\messageHistoryHandler;
use App\Core\OpenAI\OpenAICore;
use App\Core\VectorDB\PineconeCore;
use App\Core\VectorDB\VectorQueryReturn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OpenAI;
use Carbon\Carbon;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt',
        'prompt_type',
        'model_id',
        'is_active',
    ];

    public $prompt_enum = [
        'system',
        'user',
        'assistant',
        'completion',
        'function_call',
        'image',
        'other',
        'invalid'
    ];

    protected $table = 'prompts';

    public function model()
    {
        return $this->belongsTo(LLM::class);
    }

    public function getPromptById($promptIndex, $modelId){

        $promptOutput = '';
        $promptType = $this->prompt_enum[$promptIndex];
        $prompt = $this->where('prompt_type', $promptType)
            ->where('model_id', $modelId)
            ->first() ?? [];

        if(data_get($prompt,'prompt', null)==null){
            return 'No prompt found.';
        }
        if($promptType=='user'){
//            $promptOutput = $this->messageHistoryForPromptEditorUI(json_decode($prompt->prompt,true));
            //no idea what i was thinking there
            $promptOutput = $prompt->prompt;
        }else{
            $promptOutput = $prompt->prompt;
        }
        return $promptOutput;
    }

    public function initializeDBPrompts(){
        $prompt = new Prompt;

        //system prompt init/////////////////////////////////////

        $prePromptString = CommonKnowledge::selfAwareness() . CommonKnowledge::temporalAwareness() . CommonKnowledge::basicInstructions();

        $prompt->firstOrCreate([
            'prompt' => $prePromptString,
            'prompt_type' => 'system',
            'model_id' => 1,
            'is_active' => 1]
        );

        //user prompt init///////////////////////////////////////

        $messageHistory = new Messages;
        $messageCollection = $messageHistory->with('anneReply')->orderBy('created_at', 'desc')->take(5)->get();


        foreach($messageCollection as $message) {

           if($message==null) {
               continue;
           }
//           if(gettype($message)!=='string') {
//               \Log::debug(json_encode($message));
//            }else{
//               \Log::debug($message);
//           }
               $promptArray[] = [
                'assistant' => $message->anneReply->message,
                'user' => $message->user->name .': ' . $message->message,
                ];
            $messageObjectArray[] = $message;
        }
         $messageHistoryJson = json_encode($promptArray);
        $prompt->firstOrCreate([
            'prompt' => $messageHistoryJson,
            'prompt_type' => 'user',
            'model_id' => 1,
            'is_active' => 0,
        ]);

        //assistant///////////////////////////////////////

        $prompt->firstOrCreate([
            'prompt' => 'This one is tricky. This is where the non-system instructions would go, like the vector summary maybe, I dunno.',
            'prompt_type' => 'assistant',
            'model_id' => 1,
            'is_active' => 0,
        ]);

        //completion//////////////////////////////////////

        $prePromptWithHistory = $prePromptString . $this->messageHistoryForPromptEditorUI($messageObjectArray);
        $prePromptWithHistoryAndVectors = $prePromptWithHistory . $this->vectorSummaryForPromptEditorUI();
        $completedDavinciPrompt = $prePromptWithHistoryAndVectors . "You: ";

        $prompt->firstOrCreate([
            'prompt' => $completedDavinciPrompt,
            'prompt_type' => 'completion',
            'model_id' => 2,
            'is_active' => 0,
        ]);

        //duplicate for GPT4 for now

        $copy = new Prompt;
        foreach ($prompt->where('model_id', 1)->get() as $original) {
            $copy->create([
                'prompt' => $original->prompt,
                'prompt_type' => $original->prompt_type,
                'model_id' => '3',
                'is_active' => '0',
            ]);
        };

        return '';

    }


    //this is definitely iffy lmao
    public function messageHistoryForPromptEditorUI($promptArray)
    {
        $messageHistoryString = "\n\nThis is the most recent chat history, including your replies:\n\n";
        foreach ($promptArray as $userMessage) {

            $messageHistoryString .= "Timestamp: " . Carbon::parse($userMessage['created_at'])->toDateTimeString() . "\n" .
                $userMessage->user->name . ' said: ' . $userMessage->message . "\n";
            $messageHistoryString .= $userMessage->anneReply ?
                "You replied: " . $userMessage->anneReply->message . "\n" : "You did not reply to this message.\n";
        }
        return $messageHistoryString;
    }

    //sweet jesus what have i gotten myself into
    public function vectorSummaryForPromptEditorUI()
    {

        $client=OpenAI::client(getenv('OPENAI_API_KEY'));

        $openAICore = new OpenAICore;

        //get vector for user's message
        $userEmbed = $openAICore->buildEmbedding('What is a cactus cat', $client)->embeddings[0]->embedding;

        // Query pinecone with the user embedding
        $vectorQueryResult = new PineconeCore;
        $vectorQueryResult = $vectorQueryResult->query($userEmbed);

        $vectorPrompt = VectorQueryReturn::addHistoryFromVectorQuery($vectorQueryResult, null, $client);

        return $vectorPrompt['result'];
    }
}
