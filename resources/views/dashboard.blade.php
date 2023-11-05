<x-app-layout>
    <x-slot name="header">
        {{-- <h2 class="text-2xl font-mono text-ltblue-60 bg-black/70 px-fluid-2xs pt-fluid-2xs rounded-md">
            {{ __('anne.hedonia v0.0.1') }}
        </h2> --}}
    </x-slot>
    <div class="h-[calc(100vh-128px)] w-full">

        <div class="h-full grid grid-cols-7 grid-rows-4 gap-4 mx-fluid-m mb-fluid-m">
            <div class="tile h-full flex flex-col col-span-4 place-self-start w-full gap-4">

                <div class="flex items-baseline">
                    <div class="h-min text-3xl font-mono italic font-normal text-ltblue-85 mr-3">
                        anne.hedonia
                    </div>

                    <div class="h-min font-mono italic text-ltblue-85">
                        now playing
                    </div>
                </div>
                <div class="chat chat-end">

                    <div class="chat-image avatar">
                        <div class="w-10 rounded-full bg-ltblue-75">
                            <img src={{ Vite::asset('resources/img/disdain.svg')}}>
                        </div>
                    </div>

                    <div class="chat-bubble bg-midnight-500">Beating your ass at trivia!</div>
                    <div class="chat-footer opacity-50">
                        Now
                    </div>

                </div>

            </div>
            <div class="display hover:filter hover:brightness-125 transition-all ease-in-out duration-500">
            </div>
            <div class="display hover:filter hover:brightness-125 transition-all ease-in-out duration-500">
            </div>

            <div class="border border-amber-700/30 bg-black/30 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500">

            </div>

            <div class="display col-span-2 row-span-2 hover:filter hover:brightness-125 transition-all ease-in-out duration-500">

                <ul class="list-inside list-disc">
                    <h3 class="text-lg font-bold"> TODO:</h3>
                    <li>webplayer</li>
                    <li>in message history, display all interactions from the user</li>
                    <li>for logs: add sort by asc/desc, up down care, paginate?
                </ul>

                <ul class="list-inside list-disc">

                    <h3 class="font-bold text-lg">Playlist</h3>
                    <li>sort by id/numerical/post order</li>
                    <li>peek-popout on item hover</li>
                </ul>

            </div>
            <div class="display hover:filter hover:brightness-125 transition-all ease-in-out duration-500">
            </div>
            <div class="display hover:filter hover:brightness-125 transition-all ease-in-out duration-500">
            </div>
            <div class="p-fluid-s border border-midnight-100 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500">
            </div>
            <div class="p-fluid-s border border-midnight-200 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500">
            </div>
            <div class="p-fluid-s border border-amber-800/20 bg-black/20 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-amber-700/20 bg-black/30 rounded-md col-span-2 p-fluid-xs text-xs overflow-y-scroll hover:filter hover:brightness-125 transition-all ease-in-out duration-500">
            </div>
            <div class="border border-midnight-300 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-midnight-900 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-midnight-900 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-midnight-400 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-midnight-500 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-midnight-600 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-midnight-700 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-midnight-800 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-midnight-900 rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>
            <div class="border border-black rounded-md hover:filter hover:brightness-125 transition-all ease-in-out duration-500"></div>

        </div>
    </div>
</x-app-layout>
