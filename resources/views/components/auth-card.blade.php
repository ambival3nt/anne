<div class="h-screen w-full flex flex-col justify-center items-center pt-6 sm:pt-0">
    <div class="max-w-sm px-6">
        {{ $logo }}
    </div>

    {{-- the card itself --}}
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-black/40 shadow-lg shadow-black/50 border border-transparent overflow-hidden sm:rounded-md">
        {{ $slot }}
    </div>
</div>
