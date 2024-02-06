<div class="container" data-theme="mytheme">
    <div class="max-h-[calc(100dvh-64px)] overflow-y-scroll">
        <div class="px-4 py-6 place-self-start blurb">Of course I remember!</div>
        <div class="  px-fluid-m">
            @foreach ($messages as $message)
            {{-- user message --}}
            <div class="chat chat-start">
                <div class="chat-image avatar">
                    <div class="w-10 rounded-full">
                        <img src={{ $message->user->avatar }}>
                    </div>
                </div>
                <div class="chat-header">
                    <span class="pr-fluid-xs text-ltblue">{{ $message->user->name }}</span>
                    <time class="text-xs text-ltblue/60">{{ $message->created_at }}</time>

                </div>
                <div class="chat-bubble">{{ $message->message }}</div>

                {{-- <div class="chat-footer opacity-50"> --}}

            </div>
        </div>

        <div tabindex="0" class="collapse text-primary-content focus:text-secondary-content">
            <div class="collapse-title">
                {{-- anne message --}}

                <div class="chat chat-end">
                    <div class="chat-image avatar">
                        <div class="w-10 rounded-full bg-ltblue-75">
                            <img src={{ Vite::asset('resources/img/disdain.svg')}}>
                        </div>
                    </div>
                    <div class="chat-header">
                        <span class="pr-fluid-xs text-ltblue-55">anne</span>
                        <time class="text-xs text-ltblue-55 opacity-50">{{ $message->created_at }}</time>

                    </div>
                    <div class="chat-bubble">
                        {{ $message->anneReply->message }}
                    </div>
                </div>
            </div>

            <div class="collapse-content">
                <div class="chat chat-end">
                    <div class="chat-bubble bg-secondary">
                        <p>{{ $message->thoughts->summary ?? 'No thoughts.' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
</div>
</div>
