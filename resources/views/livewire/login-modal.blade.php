@if(!$showModal)
<div>
    <a wire:click="$set('showModal', true)" class="cursor-pointer hover:text-ltblue-55"> Log in
    </a>
</div>
@elseif ($showModal)
<div class="font-light min-w-[30ch] place-self-start border-lime-400">
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            {{-- <span class="input-label">Email </span> --}}
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-[2px] w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <div class="flex justify-between">
                <x-input-label for="password" :value="__('Password')" class="inline-flex" />

                @if (Route::has('password.request'))
                <a class="inline-flex self-center underline text-xs text-ltblue/50 hover:text-ltblue-55/70 rounded-sm focus:text-ltblue-55 focus:ring-ltblue-55 focus:outline-none" href="{{ route('password.request') }}">
                    {{ __('Forgot?') }}
                </a>
                @endif
            </div>

            <x-text-input id="password" class="block mt-[2px] w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-2 text-sm text-ltblue hover:text-ltblue-55 opacity-70">

            <label for="remember_me" class="inline-flex items-center">
                <input type="checkbox" id="remember_me" name="remember_me" />

                <span class="ml-2 text-xs">{{ __('Remember me') }}</span>

            </label>
        </div>


        <div class="flex justify-between mt-6">

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>

            <x-secondary-button wire:click="$set('showModal', false)" type="button" class="secondary-button">
                ✕ Cancel
            </x-secondary-button>

        </div>


    </form>

</div>
@endif
