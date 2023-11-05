<div class="grid mt-fluid-l w-[clamp(400px,_60vw,_700px)]">

{{--    TODO: Gils you should probably make my shitty dropdown look ... not shitty --}}

{{--                                        --}}
{{--    User Dropdown                       --}}
{{--                                        --}}

    <label for="users">Gils fix me</label>

    <select id="users"
            class="select select-ghost w-full max-w-xs"
            wire:model="selectedUser"
            wire:change="pageHandler('{{json_decode($paginator)->current_page }}')"
    >
        <option value="0" selected>All Users</option>
{{--        Loop the userlist, setting to id but displaying name--}}
        @foreach($userList as $id=>$name)
            <option value="{{$id}}">
                {{$name}}
            </option>
        @endforeach

    </select>


        {{--                                        --}}
        {{--        Playlist                        --}}
        {{--                                        --}}

    @foreach(json_decode($paginator)->data as $song)
    <div wire:key="item-{{ $song->id }}"
             class="playlist-item flex self-center mt-fluid-xs py-fluid-2xs px-fluid-xs rounded-md border border-midnight-500 bg-ltblack/50 hover:bg-ltblack transition-colors ease-linear duration-200">

            <span class="item"></span>
            <img class="mask mask-squircle thumbnail shrink-0 w-[clamp(60px,_40px+5vw,_200px)] h-min mr-fluid-xs"
                 src={{ $song->thumbnail }}>

            <div class="wrapper flex flex-col flex-wrap">
                <div class="track">
                    <a href="{{ $song->url }}">
                        <span class="artist text-lg text-ltblue-65">{{ $song->artist }}</span> -
                        <span class="title text-lg text-ltblue-65">{{ $song->title }}</span>
                    </a>
                </div>
                <div
                    class="details flex flex-col items-baseline gap-[0.4em] mt-fluid-xs font-mono font-light tracking-tight text-sm">
                    <div class="post">
                        Posted by
                        <span class="user text-amber-200/40">{{ $this->getUserName($song->user_id)}}</span> at
                        <span class="time">{{ $song->created_at }}</span>
                    </div>
                    <div class="source flex items-center">
                        <span class="duration">{{ $song->duration }}&nbsp;</span>
                        ► Play on&nbsp;
                        <img class="relative top-[0.1em] w-4 aspect-square filter invert"
                             src="{{Vite::asset($this->getIcon($song->source)) }}">
                        &nbsp;
                        <span class="platform">{{ $song->source }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach


    <div class="join mt-fluid-m place-self-center">
        <button type="button"
                class="join-item btn btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55"
                wire:click="pageHandler('1')">«
        </button>
        <button type="button"
                class="join-item btn btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55"
                wire:click="pageHandler('{{json_decode($paginator)->current_page - 1 }}')">«
        </button>

        <button type="button"
                class="join-item btn btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55">
            Page {{json_decode($paginator)->current_page}} / {{json_decode($paginator)->last_page}}</button>
        <button type="button"
                class="join-item btn btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55"
                wire:click="pageHandler('{{json_decode($paginator)->current_page + 1}}')">»
        </button>
        <button type="button"
                class="join-item btn btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55"
                wire:click="pageHandler('{{json_decode($paginator)->last_page}}')">»
        </button>


    </div>
</div>
