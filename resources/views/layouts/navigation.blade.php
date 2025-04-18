<nav x-data="{ open: false }" class="w-full">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto">
        <div class="flex h-16 bg-midnight border-b border-b-midnight-500 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="vignette flex w-16">
                    <a href="{{ route('dashboard') }}" class="">
                        <x-application-logo />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:mx-10 sm:flex ">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('anne,') }}
                    </x-nav-link>
                    <x-nav-link :href="route('botlogs')" :active="request()->routeIs('botlogs')">
                        {{ __('furnace') }}
                    </x-nav-link>
                    <x-nav-link :href="route('history')" :active="request()->routeIs('history')">
                        {{ __('remember') }}
                    </x-nav-link>
                    {{-- <x-nav-link :href="route('history')">
                        {{ __('show me') }}
                    </x-nav-link> --}}
                    <x-nav-link :href="route('prompt')" :active="request()->routeIs('prompt')">

                        {{ __('prompt') }}
                    </x-nav-link>
                    <x-nav-link :href="route('playlistUI')" :active="request()->routeIs('playlistUI')">


                        {{ __('playlist') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
           <div class="mr-2 hidden sm:flex flex-row-reverse sm:items-center sm:ml-6 fill-current">
               <x-dropdown width="48">
                   <x-slot name="trigger">
                       <button class="inline-flex items-center px-3 py-2 border border-midnight-500 hover:border-midnight-100 text-sm text-ltblue leading-4 font-medium rounded-md bg-ltblack/50 hover:text-ltblue hover:bg-black/50 focus:outline-none transition ease-in-out duration-150">
                           <div>{{ Auth::user()->name }}</div>

                           <div class="ml-1">
                               <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                   <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                               </svg>
                           </div>
                       </button>
                   </x-slot>

                   <x-slot name="content">
                       <x-dropdown-link :href="route('profile.edit')">
                           {{ __('Profile') }}
                       </x-dropdown-link>

                       <!-- Authentication -->
                       <form method="POST" action="{{ route('logout') }}">
                           @csrf

                           <x-dropdown-link :href="route('logout')"
                                   onclick="event.preventDefault();
                                               this.closest('form').submit();">
                               {{ __('Log Out') }}
                           </x-dropdown-link>
                       </form>
                   </x-slot>
               </x-dropdown>
           </div>

            <!-- Hamburger -->
            <div class="mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md border border-midnight-500 text-ltblue-55 hover:text-ltblue-65 hover:bg-black focus:outline-none focus:bg-black focus:text-ltblue-65 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('botlogs')" :active="request()->routeIs('botlogs')">
                {{ __('furnace') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('history')" :active="request()->routeIs('history')">
                 {{ __('remember') }}
            </x-responsive-nav-link>
            {{-- <x-responsive-nav-link :href="route('history')">
                 {{ __('show me') }}
            </x-responsive-nav-link> --}}
            <x-responsive-nav-link :href="route('prompt')" :active="request()->routeIs('prompt')">

              {{ __('prompt') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('playlistUI')" :active="request()->routeIs('playlistUI')">

              {{ __('playlist') }}
            </x-responsive-nav-link>


        </div>


        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-ltblue">
            <div class="px-4">
                <div class="font-medium text-base text-slate-600">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
