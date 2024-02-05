<div>
    <div class="flex justify-center items-center">
        <div class="join">
            <button type="button"
                    class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70"
                    wire:click="pageHandler('1')">«
            </button>
            <button type="button"
                    class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70"
                    wire:click="pageHandler('{{json_decode($logData)->current_page - 1 }}')">«
            </button>

            <button type="button"
                    class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70">
                Page {{json_decode($logData)->current_page}} / {{json_decode($logData)->last_page}}</button>
            <button type="button"
                    class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70"
                    wire:click="pageHandler('{{json_decode($logData)->current_page + 1}}')">»
            </button>
            <button type="button"
                    class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70"
                    wire:click="pageHandler('{{json_decode($logData)->last_page}}')">»
            </button>
        </div>
    </div>
    <div class="grid grid-cols-furnace grid-flow-row">

        <div class="p-fluid-s text-lg text-left font-semibold">Timestamp</div>
        <div class="p-fluid-s text-lg text-left font-semibold">Level</div>
        <div class="p-fluid-s text-lg text-left font-semibold">Message</div>

        {{--log row loop                        --}}

        @foreach (json_decode($logData)->data as $log)

            {{--            <div wire:key="log-{{$log->id}}" class="">--}}

            <div class="p-fluid-xs [&:nth-child(6n+1)]:bg-midnight-800">
                {{ $log->logged_at }}
            </div>

            <div class="p-fluid-xs [&:nth-child(6n+2)]:bg-midnight-800">
                {{ $log->level }}
            </div>

            <div
                 class="p-fluid-xs [&:nth-child(6n+3)]:bg-midnight-800 [&:nth-child(3n+3)]:break-words">
                {{ substr($log->message, 0, 250) }}
            </div>
            {{--            </div>--}}
        @endforeach

        <div class="flex justify-center items-center mt-4">
            <div class="join">
                <button type="button"
                        class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70"
                        wire:click="pageHandler('1')">«
                </button>
                <button type="button"
                        class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70"
                        wire:click="pageHandler('{{json_decode($logData)->current_page - 1 }}')">«
                </button>

                <button type="button"
                        class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70">
                    Page {{json_decode($logData)->current_page}} / {{json_decode($logData)->last_page}}</button>
                <button type="button"
                        class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70"
                        wire:click="pageHandler('{{json_decode($logData)->current_page + 1}}')">»
                </button>
                <button type="button"
                        class="join-item btn btn-sm btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55 opacity-70"
                        wire:click="pageHandler('{{json_decode($logData)->last_page}}')">»
                </button>
            </div>
        </div>

    </div>

</div>
