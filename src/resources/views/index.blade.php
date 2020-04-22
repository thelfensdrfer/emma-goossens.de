<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

        <title>{{ config('app.name') }}</title>

        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    </head>
    <body class="bg-white text-black min-h-full p-8 @night night @endnight">
        <div id="app">
            <night-mode></night-mode>

            <div class="mx-auto max-w-screen-lg relative z-10">
                <h1 class="text-center text-6xl font-bold my-16 md:my-32 lg:my-48">Emma Gooßens</h1>

                <masonry :files='{{ json_encode($files) }}'></masonry>

                <div class="mt-16 text-right">
                    <a href="{{ url('/abmelden') }}" title="Abmelden">
                        <i class="fas fa-sign-out fa-2x" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>

        @night
            <div id="particles-js"></div>
        @endnight

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
