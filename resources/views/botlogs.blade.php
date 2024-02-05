<x-app-layout>
    <x-slot name="header">

{{-- we should manipulate the dom to show the header 
    in the nav bar in mobile view --}}

        {{-- <h2>
            {{ __("Anne, furnace.") }}
        </h2> --}}
    </x-slot>

            <div class="display h-[83vh] overflow-y-scroll">

                <div class="blurb">
                    {{ __("So... maybe some stuff happened while you were gone...") }}
                </div>

                <livewire:show-logs />
            </div>
    
</x-app-layout>
