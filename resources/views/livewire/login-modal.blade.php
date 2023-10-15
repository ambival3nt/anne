@if(!$showModal)

{{-- open modal on enter key press --}}

<div>
    <a wire:click="$set('showModal', true)" wire:keydown.enter="$set('showModal', true)" tabindex="0" class="login cursor-pointer text-ltblue visited:text-ltblue"> Log in

    </a>
</div>
@elseif ($showModal)
<div>
    <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <div class="inset-0 bg-black-100 bg-opacity-75 transition-opacity"></div>

        <div class="inset-0 z-10 overflow-y-auto">
            <div class="items-end p-4 text-center sm:items-center sm:p-0">

                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg" style="border-radius: 20px; border:1px">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <span class="input-label">Email </span>
                            <x-text-input id="email" class="input-field" type="email" name="email" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div>
                            <x-input-label for="password" :value="__('Password')" class="input-label" />
                            <x-text-input id="password" class="input-field" type="password" name="password" required autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                       <div class="checkbox-wrapper">
                           <input type="checkbox" id="remember_me" name="remember_me">
                           <label for="remember_me" class="check" >
                               <svg width="16px" height="16px" viewBox="0 0 18 18">
                                   <path d="M1,9 L1,3.5 C1,2 2,1 3.5,1 L14.5,1 C16,1 17,2 17,3.5 L17,14.5 C17,16 16,17 14.5,17 L3.5,17 C2,17 1,16 1,14.5 L1,9 Z"></path>
                                   <polyline points="1 9 7 14 15 4"></polyline>
                               </svg>
                               <span class="remember_me" tabindex="0">{{ __('Remember me') }}</span>
                           </label>
                       </div>


                        <div>
                            @if (Route::has('password.request'))
                            <a class="forgot" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                            @endif

                            <x-primary-button class="login-btn">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>

                    </form>
                    <button wire:click="$set('showModal', false)" type="button" class="cancel-btn">
                        ✕ 
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endif