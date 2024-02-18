<div class="container flex h-[90vh] p-fluid-m pt-fluid-l" data-theme="mytheme">
    <div class="flex flex-col flex-grow h-full">

        {{-- control--}}


        <div class="flex flex-col">
            <div class="join align-top">
                <input class="join-item btn btn-sm btn-outline btn-primary" wire:change="changeModel" wire:model="activeModel" value="2" type="radio" name="options" aria-label="3.5 Instruct" />
                <input class="join-item btn btn-sm btn-outline btn-primary" wire:change="changeModel" wire:model="activeModel" value="1" type="radio" name="options" aria-label="3.5 Chat" />
                <input class="join-item btn btn-sm btn-outline btn-primary" wire:change="changeModel" wire:model="activeModel" value="3" type="radio" name="options" aria-label="GPT4" />
            </div>
        </div>


        {{-- prompt--}}
        {{-- <div class="flex flex-col h-full w-full"> --}}
        @if($activeModel==1 || $activeModel==3)
        <div class="pt-fluid-l">
            <input class="join-item btn btn-sm btn-outline btn-primary" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="1" aria-label="System" />
            <input class="join-item btn btn-sm btn-outline btn-primary" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="2" aria-label="User" />
            <input class="join-item btn btn-sm btn-outline btn-primary" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="3" aria-label="Assistant" />
        </div>

        @elseif($activeModel==2)
        <div class="pt-fluid-l">
            <input class="join-item btn btn-sm btn-outline btn-primary" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="4" aria-label="Instruct" />
        </div>
        @endif

        <div class="mt-fluid-s h-full">
            <textarea class="textarea textarea-primary resize-none font-mono text-xs leading-5 tracking-tight h-full w-full bg-ltblack/50 focus:outline-1 focus:outline-offset-0" spellcheck="false" wire:model="activePrompt"></textarea>
        </div>

        <div class="join mt-fluid-m">
            <input class="join-item btn btn-sm btn-outline btn-warning" wire:click="toggleModal('anne')" type="radio" name="options" aria-label="Send to Anne" />
            <input class="join-item btn btn-sm btn-outline btn-error" onclick="my_modal_4.showModal()" type="radio" name="options" aria-label="File" />
        </div>

        {{-- </div> --}}
    </div>
    {{-- right side--}}
    {{-- output--}}
    <div class="flex flex-col flex-grow pl-fluid-l-xl">

        <textarea class="textarea textarea-primary mt-fluid-xl rounded-b-none resize-none font-mono tracking-tight leading-none text-xs h-full w-full bg-transparent focus:outline-1 focus:outline-offset-0" wire:model="outputData" spellcheck="false"></textarea>


        {{-- input--}}
        <textarea class="textarea textarea-primary mb-[44px] rounded-t-none resize-none scroll-none font-mono leading-none tracking-tight text-xs w-full bg-transparent focus:outline-1 focus:outline-offset-0" wire:click="clearInput" wire:model="userInput" wire:keydown.enter="chatInput" spellcheck="false"></textarea>

    </div>


    {{-- modal--}}


    {{--TODO: move to component when i can figure out how to do so and make it fuckin work still god--}}

    {{-- the whole thing has to be a form i think?  button type submit --}}

    <dialog id="my_modal_4" class="modal">
        <div class="modal-box max-w-xl">

            <h3 class="font-bold text-lg text-ltblue-45">Import/Export Prompts</h3>

            <div class="mt-fluid-l">
                <label for="loadBox">Load </label>
                <input id="loadBox" type="file" class="file-input file-input-bordered file-input-primary file-input-xs max-w-xs" />
            </div>
            <div class="mt-fluid-l">
                <label for="loadBox">Save </label>
                <input id="saveBox" type="file" class="file-input file-input-bordered file-input-secondary file-input-xs max-w-xs" />

            </div>
            <div class="modal-action">
                <form method="dialog">
                    <!-- if there is a button, it will close the modal -->
                    <button type="submit" class="btn btn-sm btn-info">Close</button>
                </form>
            </div>
        </div>
    </dialog>


</div>
