<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

        <title>{{ config('app.name') }} | Anmelden</title>

        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    </head>
    <body class="bg-white text-black min-h-full @night night @endnight">
        <div id="app" class="flex flex-col-reverse h-screen relative z-10">
            <night-mode></night-mode>

            <div class="w-full px-8 mb-16 mt-12 flex flex-col justify-center text-center flex-grow">
                <h1 class="text-4xl lg:text-6xl font-bold">
                    Emma<br>Gooßens
                </h1>

                <hr class="block w-1/2 mx-auto my-8">

                <form method="post" action="{{ url('/anmelden') }}" class="w-1/2 mx-auto">
                    @csrf

                    @error('password')
                        <div class="bg-red-100 text-red-900 border-l-4 border-red-900 p-2 mb-8 font-bold">{{ $message }}</div>
                    @enderror

                    <label for="password" class="block mb-2">Passwort:</label>

                    <input type="password" name="password" id="password" class="block w-full text-center" placeholder="******">

                    <button type="submit" class="block w-full mt-8">Anmelden</button>
                </form>
            </div>

            <div class="w-full px-8 h-28 flex-shrink bg-emma"></div>
        </div>

        @night
            <div id="particles-js"></div>
        @endnight

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
