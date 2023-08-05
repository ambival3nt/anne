<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-waterloo leading-tight">
            {{ __('Bot logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-midnight/70 overflow-hidden shadow-sm shadow-violet3/50 sm:rounded-lg">
                <div class="p-6 text-waterloo">
                    {{ __("Logs go here") }}
                    @livewire('show-logs')
                    more logs
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
