<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'HR Management') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-indigo-50 via-white to-indigo-100">
        <div class="min-h-screen flex flex-col items-center justify-center">
            <div class="text-center">
                <x-application-logo class="w-24 h-24 mx-auto fill-current text-indigo-600" />
                <h1 class="mt-4 text-4xl font-bold text-gray-800">{{ config('app.name', 'HR Management') }}</h1>
                <p class="mt-2 text-lg text-gray-500">Streamline your workforce management</p>

                <div class="mt-8 flex items-center justify-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition">
                            Log In
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg shadow-md border border-indigo-200 hover:bg-indigo-50 transition">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <footer class="mt-16 text-center text-sm text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name', 'HR Management') }}. All rights reserved.
            </footer>
        </div>
    </body>
</html>
