<div class="container">
    <div class="flex flex-col grid grid-cols-4 grid-rows-4 gap-4">
{{--        control--}}
        <div>

            <div>
                <div class="join align-top">
                    <input class="join-item btn   btn-sm btn-outline btn-info" wire:change="changeModel" wire:model="activeModel" value="2" type="radio" name="options" aria-label="3.5 Instruct" />
                    <input class="join-item btn   btn-sm btn-outline btn-info" wire:change="changeModel" wire:model="activeModel" value="1" type="radio" name="options" aria-label="3.5 Chat" />
                    <input class="join-item btn   btn-sm btn-outline btn-info" wire:change="changeModel" wire:model="activeModel" value="3" type="radio" name="options" aria-label="GPT4" />

                    </div>
                <div>
                    I'm a control panel!
                </div>
                <div class="align-items-bottom">
                <div class="flex justify-items-end">
                <div class="join align-bottom">
                    <input class="join-item btn btn-sm  btn-outline btn-warning" type="radio" name="options" aria-label="Send to Anne" />
                    <input class="join-item btn btn-sm  btn-outline btn-secondary" type="radio" name="options" aria-label="Export" />
                </div>
                </div>
                </div>
            </div>

        </div>

{{--        prompt--}}
        <div class="flex-auto col-start-1 col-span-2 row-start-3 row-span-2">
            @if($activeModel==1 || $activeModel==3)
            <div>
                <input class="join-item btn btn-outline btn-sm btn-info" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="1" aria-label="System" />
                <input class="join-item btn btn-outline btn-sm btn-info" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="2" aria-label="User" />
                <input class="join-item btn btn-outline btn-sm btn-info" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="3" aria-label="Assistant" />
            </div>
            @elseif($activeModel==2)
                <div>
                    <input class="join-item btn btn-outline btn-sm btn-info" wire:change="getPrompt" wire:model="activePromptSection" type="radio" name="promptRadio" value="4" aria-label="Instruct" />
                </div>
            @endif
            <textarea class="textarea h-5/6 w-5/6" wire:model="activePrompt"></textarea>
        </div>

{{--        right side--}}
{{--        output--}}
        <div class="flex-auto row-span-4 col-span-2 col-start-3 row-start-1">
            <textarea class="textarea textarea-info h-5/6 w-full" placeholder="Output Goes Here"></textarea>
        </div>
    </div>

</div>

{{--    <style>--}}
{{--    textarea {--}}
{{--        display: block;--}}
{{--        max-height: fit-content;--}}

{{--    }--}}

{{--</style>--}}

