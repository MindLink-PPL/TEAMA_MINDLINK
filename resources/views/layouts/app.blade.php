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
        <div class="min-h-screen flex">
            <!-- MindLink Sidebar -->
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
                <!-- Top Bar -->
                <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Mobile Hamburger Button -->
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

                        <div>
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                                    {{ Auth::user()->isAdmin() ? 'Administrative Dashboard' : (Auth::user()->isCounsellor() ? 'Counsellor Workspace' : 'Student Dashboard') }}
                                </h1>
                            @endisset
                        </div>
                    </div>

                    <!-- Right Portal Pill (Matching Figma: "Admin portal", "Counsellor portal", "Student portal") -->
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-semibold border border-slate-200 bg-white text-slate-600 shadow-2xs">
                            {{ Auth::user()->portal_name }}
                        </span>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto">
                    <!-- Global Flash Messages -->
                    @if (session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium">{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
