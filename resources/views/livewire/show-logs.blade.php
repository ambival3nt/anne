    <div class="grid grid-cols-furnace grid-flow-row">
        <div class="p-fluid-s text-lg text-left font-semibold">Timestamp</div>
        <div class="p-fluid-s text-lg text-left font-semibold">Level</div>
        <div class="p-fluid-s text-lg text-left font-semibold">Message</div>

        @foreach ($logData as $log)
        <div class="p-fluid-xs [&:nth-child(6n+1)]:bg-midnight-700">
            {{ $log->logged_at }}
        </div>

        <div class="p-fluid-xs [&:nth-child(6n+2)]:bg-midnight-700">
            {{ $log->level }}
        </div>

        <div class="p-fluid-xs [&:nth-child(6n+3)]:bg-midnight-700 [&:nth-child(3n+3)]:break-words">
            {{ $log->message }}
        </div>
        @endforeach
    </div>