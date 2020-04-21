<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }} | Anmelden</title>

        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    </head>
    <body class="bg-white text-black min-h-full">
        <div class="flex flex-col-reverse lg:flex-row h-screen">
            <div class="w-full lg:w-1/2 px-8 mb-16 lg:mb-0 mt-4 lg:mt-0 flex flex-col justify-center text-center flex-grow lg:flex-grow-0">
                <h1 class="text-4xl lg:text-6xl font-bold">
                    <span class="hidden lg:inline-block">
                        Emma<br>Gooßens
                    </span>
                    <span class="inline lg:hidden">
                        Emma Gooßens
                    </span>
                </h1>

                <hr class="block w-1/2 mx-auto my-8">

                <form method="post" action="{{ url('/anmelden') }}" class="w-1/2 mx-auto">
                    @csrf

                    @error('password')
                        <div class="bg-red-100 text-red-900 border-l-4 border-red-900 p-2 mb-8 font-bold">{{ $message }}</div>
                    @enderror

                    <label for="password" class="inline-block mr-4 mb-2">Passwort:</label>

                    <input type="password" name="password" id="password" placeholder="******">

                    <button type="submit" class="block w-full mt-8">Anmelden</button>
                </form>
            </div>

            <div class="w-full lg:w-1/2 px-8 h-full flex-shrink lg:flex-shrink-0 bg-emma"></div>
        </div>

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
