<div class="container flex h-[85vh] p-[--space-m]">
    <div class="flex flex-grow flex-col justify-around">

        {{-- control--}}


        <div class="flex flex-col">
            <div class="join align-top">
                <input class="join-item btn   btn-sm btn-outline btn-primary " wire:change="changeModel" wire:model="activeModel" value="2" type="radio" name="options" aria-label="3.5 Instruct" />
                <input class="join-item btn   btn-sm btn-outline btn-primary" wire:change="changeModel" wire:model="activeModel" value="1" type="radio" name="options" aria-label="3.5 Chat" />
                <input class="join-item btn   btn-sm btn-outline btn-primary" wire:change="changeModel" wire:model="activeModel" value="3" type="radio" name="options" aria-label="GPT4" />
            </div>
        </div>


        {{-- prompt--}}
        <div class="flex flex-col basis-4/5 w-full">
            @if($activeModel==1 || $activeModel==3)
            <div>
                <input class="join-item btn btn-outline btn-sm btn-primary" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="1" aria-label="System" />
                <input class="join-item btn btn-outline btn-sm btn-primary" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="2" aria-label="User" />
                <input class="join-item btn btn-outline btn-sm btn-primary" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="3" aria-label="Assistant" />
            </div>
            @elseif($activeModel==2)
            <div>
                <input class="join-item btn btn-outline btn-sm btn-primary" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="4" aria-label="Instruct" />
            </div>
            @endif
            <div class="mt-[--space-s] h-full ">
                <textarea class="textarea textarea-primary resize-none font-mono text-xs leading-[1.1] h-full w-full bg-ltblack/50" spellcheck="false" wire:model="activePrompt"></textarea>



            </div>
            <div class="join mt-[--space-m]">
                <input class="join-item btn btn-sm  btn-outline btn-warning" type="radio" name="options" aria-label="Send to Anne" />
                <input class="join-item btn btn-sm  btn-outline btn-accent" type="radio" name="options" aria-label="Export" />
            </div>

        </div>
    </div>
    {{-- right side--}}
    {{-- output--}}
    <div class="flex flex-col flex-grow pl-[--space-l-xl] justify-evenly">

        <textarea class="textarea textarea-primary resize-none font-mono leading-none text-xs h-[80%] place-self-center w-full bg-ltblack/50" spellcheck="false" placeholder="Output Goes Here"></textarea>

    {{-- input--}}
        <textarea class="textarea textarea-primary resize-none font-mono leading-none text-xs  place-self-center w-full bg-ltblack/50" spellcheck="false" placeholder="Say something..."></textarea>

    </div>
</div>
