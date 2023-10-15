<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo />
            </a>
        </x-slot>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />

                <x-text-input 
                    id="email" 
                    class="block mt-1 w-full" 
                    type="email" 
                    name="email" 
                    :value="old('email')" 
                    required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input 
                    id="password" 
                    class="block mt-1 w-full"
                    type="password"
                    name="password"
                    required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center text-ltblue hover:text-ltblue-55">
                    <input id="remember_me" type="checkbox" class="rounded bg-ltblack border-ltblue/50 shadow-sm outline-none focus:outline-none focus:ring-1 focus:ring-ltblue focus:ring-offset-1 focus:ring-offset-midnight" name="remember">
                    <span class="ml-2 text-sm hover:text-ltblue-55">{{ __('Remember me') }}</span>
                </label>
            </div>

            {{-- Forgot password? --}}
            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="
                        underline text-sm text-ltblue/50 hover:text-ltblue-55/70 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-ltblue focus-visible:ring-offset-2 focus-visible:ring-offset-midnight focus:text-ltblue-55 focus:ring-2 focus:ring-ltblue-55 transition-all ease-in-out duration-200"

                    href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button class="ml-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
