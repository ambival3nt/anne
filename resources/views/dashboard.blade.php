<x-app-layout>
    <x-slot name="header">
        {{-- <h2 class="text-4xl font-mono text-ltblue-75">
            {{ __('anne.hedonia v2.0.1') }}
        </h2> --}}
    </x-slot>
    <div class="w-full">
        <div class="h-[calc(90vh-64px)] grid grid-cols-7 grid-rows-4 gap-4 mx-fluid-m">
            <div class="display col-span-5">
                <h3 class="text-4xl font-mono text-ltblue-55">anne.hedonia</h3>
                <p class="text-xs font-light itali">animate this or some shit</p>
            </div>

            <div class="display col-span-2 rounded-md h-1/2 w-full place-self-end">
            </div>

            <div class="display border border-midnight-500 col-span-2 row-span-2">
            </div>

            <div class="display col-span-2">
            </div>

            <div class="border border-midnight-700 rounded-md">
            </div>

            <div class="row-span-2 border border-midnight-500 rounded-md">
            </div>
            <div class="border border-midnight-500 rounded-md">
            </div>


            <div class="border border-midnight-500 rounded-md">
            </div>

            <div class="display border border-amber-800/30 rounded-md col-span-2 row-span-3 p-fluid-xs">
            </div>

            {{-- <div class="h-1/2 w-full border border-midnight-700 rounded-md">
            </div> --}}

            <div class="display col-span-2 w-3/4 border border-midnight-400 rounded-md">
            </div>


        </div>
    </div>
</x-app-layout>
