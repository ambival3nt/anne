<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Disdain</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Mono:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="{{ Vite::asset('resources/css/anneStyle.css') }}">
        
        @livewireStyles
    </head>
    <body>
        <div class="welcome">
            <div>

                <div class="image"><img src="{{ Vite::asset('resources/img/disdain.svg') }}"></div>

                <div class="modal">
                    <h1>Disdain v.0.1.1</h1>
                    <div><livewire:login-modal></livewire:login-modal></div>
                    
                    <div><a href="{{ route('register') }}">Register</a></div>
                </div>

            </div>

            <p>Disdain is an open source and shitty Discord bot written in PHP v{{ PHP_VERSION }} by ficetyeis, styled poorly by gils.</p>
        </div>

        @livewireScripts
    </body>
</html>