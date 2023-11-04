    <div class="max-w-screen-xl mx-auto">
        <div class="">
            <div class="px-4 py-6 place-self-start blurb">Of course I remember!</div>
            <div class="max-h-[calc(100vh-128px)] overflow-y-scroll px-fluid-m">
                @foreach ($messages as $message)
                {{-- user message --}}
                <div class="chat chat-start">
                    <div class="chat-image avatar">
                        <div class="w-10 rounded-full">
                            <img src={{ $message->user->avatar }}>



                        </div>
                    </div>
                    <div class="chat-header">
                        <span class="pr-fluid-xs">{{ $message->user->name }}</span>
                        <time class="text-xs opacity-50">{{ $message->created_at }}</time>

                    </div>
                    <div class="chat-bubble">{{ $message->message }}</div>

                    <div class="chat-footer opacity-50">

                    </div>
                </div>

                {{-- anne message --}}
                <div class="chat chat-end">
                    <div class="chat-image avatar">
                        <div class="w-10 rounded-full bg-ltblue-75">
                            <img src={{ Vite::asset('resources/img/disdain.svg')}}>
                        </div>
                    </div>
                    <div class="chat-header">
                        <span class="pr-fluid-xs">anne</span>
                        <time class="text-xs opacity-50">{{ $message->created_at }}</time>

                    </div>
                    <div class="chat-bubble">{{ $message->anneReply->message }}</div>
                    {{-- anne thoughts --}}
                    <div class="chat-bubble bg-zinc-950 text-white/50">{{ $message->thoughts->summary ?? 'No thoughts.' }}</div>



                    <div class="chat-footer opacity-50">

                    </div>
                </div>


                <!--
    <div class="history">
        <p>
        <ol>
            <li class="border-l-2 border-ltblue">
                <div class="md:flex flex-start">
                    {{-- <div class="bg-purple-600 w-10 h-6 flex items-center justify-center rounded-full -ml-3.5"> --}}
                        {{-- <svg aria-hidden="true" focusable="false" data-prefix="fas" class="text-white w-3 h-3" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"> --}}
                            {{-- <path fill="currentColor" d="M0 464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V192H0v272zm64-192c0-8.8 7.2-16 16-16h288c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16v-64zM400 64h-48V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H160V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H48C21.5 64 0 85.5 0 112v48h448v-48c0-26.5-21.5-48-48-48z"></path> --}}
                        {{-- </svg> --}}
                    {{-- </div> --}}

                    {{-- user message --}}
                    <div class="block p-6 rounded-lg shadow-lg bg-ltblack border border-midnight-500 xl:max-w-2xl lg:max-w-xl md:max-w-lg-sm:max-w-md ml-6 mb-10 space-y-3">
                        <div class="flex justify-between mb-4">
                            <a href="#!" class="font-medium text-ltblue-65 hover:text-ltblue-txt focus:text-ltblue-55 duration-300 transition ease-linear text-sm"> {{ $message->user->name }} </a>
                            <a href="#!" class="pl-4 font-medium text-ltblue-65 hover:text-ltblue-txt focus:text-purple-800 duration-300 transition ease-linear text-sm"> {{ $message->created_at }}</a>
                        </div>
                        <div class="mb-4">
                            <span class="flex justify-between items-center font-medium list-none">
                                {{ $message->message }}
                            </span>
                        </div>
                    </div>

                    {{-- anne message --}}
                    <div class="block p-6 rounded-lg shadow-lg bg-midnight border border-midnight-500 xl:max-w-2xl lg:max-w-xl md:max-w-lg sm:max-w-md ml-6 mb-10 space-y-3">

                        <div class="flex justify-between mb-4">
                            <a href="#!" class="font-medium text-ltblue-75 hover:text-ltblue-txt focus:text-purple-800 duration-300 transition ease-linear text-sm"> anne</a>
                            <a href="#!" class="font-medium text-purple-600 hover:text-white focus:text-purple-800 duration-300 transition ease-linear text-sm"> {{ $message->created_at }}</a>
                        </div>


                        {{-- anne popout thoughts--}}
                        <details class="group">
                            <summary class="flex justify-between items-center font-medium cursor-pointer list-none">
                                <span>{{ $message->anneReply->message }}</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24">
                                        <path d="M6 9l6 6 6-6"></path>
                                    </svg>
                                </span>
                            </summary>
                            <p class="text-indigo-300 mt-3 group-open:animate-fadeIn">
                                {{ $message->thoughts->summary ?? 'No thoughts.' }}
                            </p>
                        </details>

                    </div>
                </div>
            </li>
        </ol>

    </div>
    -->




                @endforeach
            </div>
        </div>

