<?php

namespace App\Http\Livewire;

use App\Models\LLM;
use App\Models\Prompt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use OpenAI;


//Ok... This is probably terribly executed but its my first livewire component for reals so... deal with it. lol.
class PromptInterface extends Component
{

    //All data you want to use on the frontend must be declared here, and public. So these are the things I want to either display, use, or change.
    public $activePrompt = "";
    public $output = "";
    public $activeModel = 2;
    public $activePromptSection = 4;
    public $showModal = false;
    public $modalData = '';

    //chat interface
    public $outputData = "Output Data.";
    public $userInput = "Press enter to send.";

    //This function renders the view you give it whenever the route for this component is called. See web.php for the route declaration, as an example.
    public function render()
    {
        return view('livewire.prompt-interface');
    }

    //The mount function does everything you want to do when the component is first loaded. Once only.
    public function mount(){

        // Any laravel model you want to use has to first be assigned to something like these two. (Note: 'model' means something
        // different in the context of laravel, and the context of LLMs. Try not to let that confuse.)
        $aiModels = new LLM;
        $prompts = new Prompt;

        // Here we check to see if the database even has llms in it. If not, we initialize.
        if($aiModels->all()->count() < 1) {
            $aiModels->initializeDBModels();
            $prompts->initializeDBPrompts();
        }

        // Grabs the active llm. that is, the one that anne is currently using to respond to messages.
        $activeModelActual = $aiModels->where('is_active', 1)->first();

        // $this->variableName is how you access variables within a class in PHP. Like the public variables we defined up top.

        $this->activeModel = $activeModelActual->id;        // (public $activeModel = "");

        // Then we use that id to get the active prompt for that llm.

        $this->activePromptSection = $aiModels->find($this->activeModel)->active_prompt_id;  // (public $activePromptSection = 0;)

        //$this->functionName() is how functions are called within a class in PHP. You'll see this function right below.
        $this->getPrompt();
    }

    ///////////////////////////////pls read me pls/////////////////////////////////
    // Note: The above is very important. The absolute must-understand. Assigning anything to those public variables at the top is what updates
    // the view on the frontend. The second those change, so does anywhere in the view they are used, be that {{ $likeThis }} or @if($likeThis) or
    // @foreach($likeThis as $likeThat), or value="likeThis" or whatever.
    //
    // Returns are **NOT NECESSARY** with this style of programming. You can just assign the value to the variable and it will work. You can return
    // if you need to, or even if you just want to, but what is important to understand is that it's the variable assignments that matter most.
    ///////////////////////////////////////////////////////////////////////////////////


    /**
     * Gets the currently selected prompt via the id clicked in the UI
     * @return void
     */
    public function getPrompt() : void {
        //because $activePromptSection is connected on the frontend (see the html for the model selector), changing it there, changes $activePromptSection
        //and thus $this->activePromptSection will have the id we need without having to pass it in as a parameter.
        $prompt = new Prompt;
        $this->activePrompt = $prompt->getPromptById($this->activePromptSection, $this->activeModel) ??  'Cannot find prompt.';
    }

    /**
     * When the model is changed, this function handles selecting its active prompt.
     * @return void
     */
    public function changeModel(){
        $model = new LLM;
        $prompt = new Prompt;
        $llm = $model->find($this->activeModel); //notice how these are used just like tables. That's because they have defined classes in the Models folder.
//        dd($this->activePromptSection);
        $this->activePromptSection = $llm->active_prompt_id; //active_prompt_id is a column in the llms table.
        $this->activePrompt= $prompt->find($this->activePromptSection)->prompt; //prompt is a column in the prompts table.
    }

    public function toggleModal($modalName){

        switch($modalName){
            case 'file':
                $this->modalData = 'file';
                $this->showModal = true;
                break;
            case 'anne':
                $this->modalData = 'anne';
                $this->showModal = true;
                break;
            default:
//                $this->showModal=false;
        }
    }

    public function chatInterface(){

    }

    public function clearInput(){
        $this->userInput = '';
    }

    public function chatInput(){

        $user = Auth::user();

        $input = $user->name . ": " . $this->userInput;

        $this->outputData = $this->outputData . "\n\n$user->name:\n\t" . $this->userInput;

        $this->userInput = '';


        switch($this->activeModel){
            case 1: // gpt 3.5
                $data = $this->getGpt3ChatResponse($input);
                break;
            case 2: // 3.5 instruct
                $data = $this->getInstructResponse($input);
                break;
            case 3: // gpt 4
                $data = $this->getGpt4ChatResponse($input);
                break;
                //
            default:
                return [
                    'success' => false,
                    'message' => "No model is selected",
                    ];
        }

        $this->userInput = '';

        //then what

    }

    public function getGpt3ChatResponse($userMessage){

        $client=OpenAI::client(getenv('OPENAI_API_KEY'));

        //get the recent history
        $messageHistory = $this->buildMessageHistoryForGpt();

        //add the current message to the message history array
        $messageHistory[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        $stream = $client->chat()->createStreamed([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messageHistory,
        ]);

        $this->outputData = $this->outputData . "\n\nAnne:\n\t";


        foreach($stream as $response){
            $response->choices[0]->toArray();
            $this->outputData = $this->outputData . $response->choices[0]->delta->content;
        }
// 1. iteration => ['index' => 0, 'delta' => ['role' => 'assistant'], 'finish_reason' => null]
// 2. iteration => ['index' => 0, 'delta' => ['content' => 'Hello'], 'finish_reason' => null]
// 3. iteration => ['index' => 0, 'delta' => ['content' => '!'], 'finish_reason' => null]
// ...
    }

    public function getInstructResponse(){

    }

    public function getGpt4ChatResponse(){

    }

    //builds the message history for the gpt-3.5 model
    private function buildMessageHistoryForGpt()
    {
        $prompt = new Prompt();

        //json is saved in the table to construct an array of messages
        $prompt = $prompt->where('model_id', $this->activeModel)->where('prompt_type', 'user')->first();
        $prompt =array_reverse(json_decode($prompt->prompt));

        $historyArray = [];

        foreach($prompt as $message){
            $historyArray[] = [
                'role' => 'user',
                'content' => $message->user,
            ];
            $historyArray[] = [
                'role' => 'assistant',
                'content' => $message->assistant,
            ];
        }

        return $historyArray;
    }

}
