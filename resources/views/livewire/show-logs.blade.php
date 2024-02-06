<div data-theme="mytheme" class="h-full overflow-y-auto mt-fluid-l">

    <div class="flex justify-center items-center">
        <div class="join">
            <button type="button" class="join-item btn btn-sm btn-secondary btn-primary opacity-50" wire:click="pageHandler('1')">«
            </button>
            <button type="button" class="join-item btn btn-sm btn-secondary btn-primary opacity-50" wire:click="pageHandler('{{json_decode($logData)->current_page - 1 }}')">«
            </button>

            <button type="button" class="join-item btn btn-sm btn-secondary btn-primary opacity-50">
                Page {{json_decode($logData)->current_page}} / {{json_decode($logData)->last_page}}</button>
            <button type="button" class="join-item btn btn-sm btn-secondary btn-primary opacity-50" wire:click="pageHandler('{{json_decode($logData)->current_page + 1}}')">»
            </button>
            <button type="button" class="join-item btn btn-sm btn-secondary btn-primary opacity-50" wire:click="pageHandler('{{json_decode($logData)->last_page}}')">»
            </button>
        </div>
    </div>
<div class="bx mt-fluid-m">


    <div class="grid grid-cols-furnace grid-flow-row ">
        <div class="p-fluid-s text-lg text-left font-semibold">Timestamp</div>
        <div class="p-fluid-s text-lg text-left font-semibold">Level</div>
        <div class="p-fluid-s text-lg text-left font-semibold">Message</div>
    </div>

    <div class="grid grid-cols-furnace grid-flow-row">
        {{--log row loop                        --}}

        @foreach (json_decode($logData)->data as $log)

        {{-- <div wire:key="log-{{$log->id}}" class="">--}}

        <div class="p-fluid-xs [&:nth-child(6n+1)]:bg-midnight-800">
            {{ $log->logged_at }}
        </div>

        <div class="p-fluid-xs [&:nth-child(6n+2)]:bg-midnight-800">
            {{ $log->level }}
        </div>

{{-- TODO: truncate at ~4 lines with caret to expand --}}

        <div class="p-fluid-xs [&:nth-child(6n+3)]:bg-midnight-800 [&:nth-child(3n+3)]:break-words limiter">
            {{ $log->message }}
        </div>
        {{-- </div>--}}
        @endforeach

    </div>


</div>
</div>
