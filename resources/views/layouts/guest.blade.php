<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MindLink') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased h-full bg-[#f4f7fa] text-slate-800">
        <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6">
            <!-- Brand Logo -->
            <div class="mb-6 text-center">
                <a href="/" class="inline-flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-full bg-[#0d9488] flex items-center justify-center text-white font-bold text-2xl shadow-md">
                        M
                    </div>
                    <div>
                        <div class="text-[#0f2a4a] font-extrabold text-2xl tracking-tight leading-tight">MindLink</div>
                        <div class="text-xs text-slate-500 font-medium">Support platform</div>
                    </div>
                </a>
            </div>

            <!-- Card Box -->
            <div class="w-full sm:max-w-md bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8">
                {{ $slot }}
            </div>

            <!-- Footer note -->
            <div class="mt-6 text-center text-xs text-slate-400">
                MindLink &bull; Role-based institutional security
            </div>
        </div>
    </body>
</html>
