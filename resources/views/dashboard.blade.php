<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('anne.hedonia v0.0.1') }}
        </h2>
    </x-slot>
    <div class="w-full">
    <div class="h-[calc(90vh-64px)] grid grid-cols-7 grid-rows-4 gap-4 mx-fluid-m">
        <div class="display col-span-5">
            <ul>
                TODO:
                <li>add a display for the current song playing (nice suggestion copilot)</li>
                <li>make it so the bot can play music</li>
                <li>in message history, display all interactions from the user</li>
                <li>for logs: add sort by asc/desc, up down caret
            </ul>
        </div>
        <div class="display">
            last anne message
        </div>
        <div class="border border-ltblue-75 rounded-md">
        
        </div>

        <div class="display col-span-2 row-span-2">
            <ul>
            <h3 class="font-bold text-lg">Playlist</h3>
            <li>- sort by id/numerical/post order</li>
            <li>- peek-popout on item hover</li>


        </div>
        <div class="display">
            <h3>trivia</h3>
        </div>
        <div class="display">uhh
        </div>
        <div class="p-fluid-s border border-midnight-100 rounded-md">
        it would be kind of cool to keep these
        </div>
        <div class="p-fluid-s border border-midnight-200 rounded-md">
        maybe have them highlight-track the mouse
        </div>

<div class="border border-amber-700/30 rounded-md col-span-2 row-span-3 p-fluid-xs overflow-y-scroll">
normal stuff
<span class="font-mono">
and spline sans mono
</span>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
and i scroll
    <p class="font-mono">at least i think i scroll by now</p>

</div>

<div class="border border-midnight-300 rounded-md">

</div>
<div class="border border-midnight-500 rounded-md">
</div>
<div class="border border-midnight-400 rounded-md">
</div>
<div class="border border-midnight-600 rounded-md">
</div>

    </div>
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
