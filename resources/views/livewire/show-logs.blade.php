    <div class="furnace">

        <div>Timestamp</div>
        <div>Level</div>
        <div>Message</div>

        @foreach ($logData as $log)

        <div>
            {{ $log->logged_at }}
        </div>

        <div>
            {{ $log->level }}
        </div>

        <div>
            {{ $log->message }}
        </div>


        @endforeach

    </div>