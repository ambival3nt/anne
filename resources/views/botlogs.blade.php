<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __("Anne, furnace.") }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-orange-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-orange-400">
                    {{ __("So... maybe some stuff happened while you were gone...") }}
                    <livewire:show-logs />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
