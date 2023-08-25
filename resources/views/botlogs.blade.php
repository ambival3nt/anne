<x-app-layout>
    <!-- <x-slot name="header">
        <h2>
            {{ __("Anne, furnace.") }}
        </h2>
    </x-slot> -->

            <div class="display">

                <div class="blurb">
                    {{ __("So... maybe some stuff happened while you were gone...") }}
                </div>

                <livewire:show-logs />
            </div>
    
</x-app-layout>
