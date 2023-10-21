<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('anne.hedonia v0.0.1') }}
        </h2>
    </x-slot>
    <div class="grid grid-cols-5 grid-rows-5  mx-fluid-m">
        <div class="display col-span-5">
            <ul>
                TODO:
                <li>add a display for the current song playing (nice suggestion copilot)</li>
                <li>make it so the bot can play music</li>
                <li>in message history, display all interactions from the user</li>
                <li>okay calm down copilot</li>
            </ul>
        </div>
        <div class="display">
            last anne message
        </div>
        <div class="display">playlist</div>
        <div class="display">trivia</div>
    </div>


    {{-- <div class="py-12">--}}
    {{-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">--}}
    {{-- <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">--}}
    {{-- <div class="p-6 text-gray-900">--}}
    {{-- {{ __("You're logged in!") }}--}}
    {{-- </div>--}}
    {{-- </div>--}}
    {{-- </div>--}}
    {{-- </div>--}}
    {{-- </div>--}}
</x-app-layout>
