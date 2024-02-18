<x-app-layout>
    {{-- <x-slot name="header"> --}}

        {{-- we should manipulate the dom to show the header 
    in the nav bar in mobile view --}}

        {{-- <h2>
            {{ __("Anne, furnace.") }}
        </h2> --}}
    {{-- </x-slot> --}}


    <div data-theme="mytheme" class="chat chat-start place-self-start ml-fluid-s mt-fluid-2xs w-prose font-mono text-sm">
        <div class="chat-bubble chat-bubble-info text-ltblue border border-midnight-700 italic">
        {{-- what if we somehow made an output of what she thought of recent
        log events and put it here??? --}}
        {{-- also have these on a contdown to fade out slowly after page load --}}
        {{ __("So... maybe some stuff happened while you were gone...") }}
        </div>
    </div>

    <livewire:show-logs />

</x-app-layout>

