@if(!$showModal)
<div>
    <a class="text-sm text-blue-700 dark:text-gray-500" wire:click="$set('showModal', true)"> Log in
    </a>
</div>
@elseif ($showModal)
<div>
    <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">

            <div class="inset-0 bg-black-100 bg-opacity-75 transition-opacity"></div>

            <div class="inset-0 z-10 overflow-y-auto">
                <div class="items-end p-4 text-center sm:items-center sm:p-0">

                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        style="border-radius: 20px; border:1px">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email Address -->
                            <div>
                                Email
                                <x-text-input id="email" class="block mt-1" type="email"
                                              name="email" :value="old('email')" required
                                              style="border: #1a202c"
                                              autofocus/>
                                <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                            </div>

                            <!-- Password -->
                            <div class="mt-4">
                                <x-input-label for="password" :value="__('Password')"/>

                                <x-text-input id="password" class="block mt-1 w-full"
                                              type="password"
                                              name="password"
                                              required autocomplete="current-password"/>

                                <x-input-error :messages="$errors->get('password')" class="mt-2"/>
                            </div>

                            <!-- Remember Me -->
                            <div class="block mt-4">
                                <label for="remember_me" class="inline-flex items-center">
                                    <input id="remember_me" type="checkbox"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                           name="remember">
                                    <span
                                        class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                @if (Route::has('password.request'))
                                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                       href="{{ route('password.request') }}">
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif

                                <x-primary-button class="ml-3">
                                    {{ __('Log in') }}
                                </x-primary-button>
                            </div>
                        </form>
                        <button wire:click="$set('showModal', false)" type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
       </div>
</div>
    @endif
