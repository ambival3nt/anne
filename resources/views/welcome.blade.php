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



        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles


    </head>
    <body class="bg-midnight text-ltblue h-[100dvh]">

    <div class="flex flex-col max-w-4xl max-h-[90%] mx-auto pt-fluid-2xl px-fluid-l">

        <div class="flex gap-5 max-h-min self-center">

                <div class="min-w-[30ch]">
                    <img src="{{ Vite::asset('resources/img/disdain.svg') }}" class="min-w-[30ch]">
                </div>


                <div class="flex flex-col min-w-[30ch]">
                    <h1 class="min-w-max text-xl font-medium mb-5 place-self-start">Disdain v.0.2</h1>

                    <livewire:login-modal>
                    
                    <div class="flex h-full mt-4 hover:text-ltblue-55">
                        <a href="{{ route('register') }}">Register</a>
                    </div>
                </div>
            

        </div>

        <p class="text-xs text-ltblue/50 self-center w-[70%] text-center my-fluid-xl">Disdain is an open source and shitty Discord bot written in PHP v{{ PHP_VERSION }} by ficetyeis, styled poorly by gils.</p>

    </div>
        @livewireScripts
    </body>
</html>