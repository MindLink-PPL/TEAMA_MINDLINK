<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MindLink') }} - @yield('header_title', 'Wellbeing self check-in')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased h-full bg-[#f4f7fa] text-slate-800">
    <div class="min-h-screen flex">
        <!-- Unified MindLink Sidebar -->
        @include('layouts.navigation')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
            <!-- Top Bar -->
            <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('toggle-sidebar'))"
                        class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition focus:outline-none"
                        aria-label="Toggle Navigation"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        @yield('header_title', 'Wellbeing self check-in')
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-semibold border border-slate-200 bg-white text-slate-600 shadow-2xs">
                        {{ Auth::user()?->portal_name ?? 'Student portal' }}
                    </span>
                </div>
            </header>

            <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
