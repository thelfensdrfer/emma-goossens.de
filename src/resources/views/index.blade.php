<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
        <script src="{{ asset('js/app.js') }}" defer async></script>
    </head>
    <body class="bg-white text-black min-h-full p-8 night">
        <div id="app" class="mx-auto max-w-screen-lg">
            <h1 class="text-center text-6xl font-bold my-16 md:my-32 lg:my-48">Emma Gooßens</h1>

            <masonry :files='{{ json_encode($files) }}'></masonry>
        </div>

        <div id="particles-js" class="fixed inset-0 z-0"></div>
    </body>
</html>
