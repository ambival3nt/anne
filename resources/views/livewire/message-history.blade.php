    <div class="max-w-screen-xl mx-auto">

        <div class="">

            <div class="px-4 py-6 place-self-start"><i>Of course I remember!</i></div>
            <div class="max-h-[calc(100dvh-140px)] overflow-y-scroll px-fluid-m">
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

                    <div class="chat-footer opacity-50">
                        dropdown here?
                    </div>

                    {{-- anne thoughts --}}
                    
                    <div class="chat-bubble bg-zinc-950 text-white/50">
                        {{ $message->thoughts->summary ?? 'No thoughts.' }}
                    </div>

                    <div class="chat-footer opacity-50">

                    </div>
                </div>

                @endforeach
            </div>
        </div>
