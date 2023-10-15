<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Disdain</title>

        <!-- Fonts -->
        {{-- <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"> --}}
        {{-- <link rel="stylesheet" href="{{ Vite::asset('resources/css/welcome.css') }}"> --}}
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/anneStyle.css') }}">
        
        @livewireStyles
    </head>
    <body>
        <div class="welcome">
            <div>

                <div class="image"><img src="{{ Vite::asset('resources/img/disdain.svg') }}"></div>

                <div class="modal">
                    <h1>Disdain v.0.1.1</h1>
                    <div class="login"><livewire:login-modal></livewire:login-modal></div>
                    
                    <div><a href="{{ route('register') }}" class="register">Register</a></div>
                </div>

            </div>

            <p>Disdain is an open source and shitty Discord bot written in PHP v{{ PHP_VERSION }} by ficetyeis</p>
        </div>

        @livewireScripts
    </body>
</html>