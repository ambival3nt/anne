<div class="grid mt-fluid-l w-[clamp(400px,_60vw,_700px)]">

    {{--    TODO: Gils you should probably make my shitty dropdown look ... not shitty --}}

    {{--                                        --}}
    {{--    User Dropdown                       --}}
    {{--                                        --}}

    <div class="flex flex-row gap-4">
        <div class="basis-1/2">
            {{-- <label for="users" class="text-ltblue-55 ml-1">sort</label> --}}
            <select id="users"
                    class="select w-full max-w-xs border border-midnight-300 hover:brightness-110 focus:outline-1 focus:outline-midnight-100 focus:outline-offset-0"

                    wire:model="selectedUser"
                    wire:change="pageHandler('{{json_decode($displayData)->current_page }}')"
            >
                <option value="0" selected>All Users</option>
                {{--        Loop the userlist, setting to id but displaying name--}}
                @foreach($userList as $id=>$name)
                    <option value="{{$id}}">
                        {{$name}}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="basis-1/2">
            {{-- <label for="search">search</label> --}}
            <input type="text"
                   id="search"
                   placeholder="Search anything..."
                   class="input input-bordered border border-midnight-300 w-full max-w-xs hover:brightness-110 focus:outline-1 focus:outline-midnight-100 focus:outline-offset-0"
                   wire:model="searchText"
                   wire:change="searchHandler"
            />
        </div>
    </div>
    {{--                                        --}}
    {{--        Playlist                        --}}
    {{--                                        --}}

    @foreach(json_decode($displayData)->data as $song)
        <div wire:key="item-{{ $song->id }}"
             class="playlist-item flex self-center mt-fluid-xs py-fluid-2xs px-fluid-xs rounded-md border border-midnight-500 hover:border hover:border-midnight-300 bg-ltblack/50 hover:bg-ltblack transition-colors ease-linear duration-300">

            {{-- <span class="item"></span> --}}
            <img class="mask mask-squircle thumbnail shrink-0 w-[clamp(60px,_40px+5vw,_200px)] h-min mr-fluid-xs"
                 src={{ $song->thumbnail }}>

            <div class="wrapper flex flex-col flex-wrap">
                <div class="track">
                    <a href="{{ $song->url }}">
                        <span class="artist font-[500] text-[1.1rem] text-ltblue-65">{{ $song->artist }} - {{ $song->title }}</span>
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
                wire:click="pageHandler('{{json_decode($displayData)->current_page - 1 }}')">«
        </button>

        <button type="button"
                class="join-item btn btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55">
            Page {{json_decode($displayData)->current_page}} / {{json_decode($displayData)->last_page}}</button>
        <button type="button"
                class="join-item btn btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55"
                wire:click="pageHandler('{{json_decode($displayData)->current_page + 1}}')">»
        </button>
        <button type="button"
                class="join-item btn btn-outline btn-primary border-ltblue-55 text-ltblue-55 fill-ltblue-55"
                wire:click="pageHandler('{{json_decode($displayData)->last_page}}')">»
        </button>


    </div>
</div>
