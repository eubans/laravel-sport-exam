<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? 'Player Profiles' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
        <link href="/styles.css" rel="stylesheet" />
    </head>
    <body>
        <noscript>
            <strong>We're sorry but this page doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
        </noscript>

        <div id="app"></div>

        <script src="{{ mix('/js/app.js') }}"></script>
    </body>
</html>
