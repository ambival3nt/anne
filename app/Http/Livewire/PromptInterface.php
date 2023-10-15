<?php

namespace App\Http\Livewire;

use App\Models\LLM;
use App\Models\Prompt;
use Livewire\Component;


//Ok... This is probably terribly executed but its my first livewire component for reals so... deal with it. lol.
class PromptInterface extends Component
{

    //All data you want to use on the frontend must be declared here, and public. So these are the things I want to either display, use, or change.
    public $activePrompt = "";
    public $output = "";
    public $activeModel = 2;
    public $activePromptSection = 4;


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
}
