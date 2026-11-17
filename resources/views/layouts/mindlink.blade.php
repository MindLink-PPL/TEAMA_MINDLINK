<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindLink - Wellbeing Check-in</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f4f7f9] font-sans antialiased text-slate-900">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-60 bg-[#12263a] text-white flex flex-col justify-between shrink-0 hidden md:flex">
            <div>
                <div class="p-6 flex items-center space-x-3">
                    <div class="w-9 h-9 bg-[#2a9d8f] rounded-full flex items-center justify-center font-bold text-white text-sm">M</div>
                    <div>
                        <h1 class="font-bold text-lg leading-none">MindLink</h1>
                        <span class="text-xs text-slate-300">Support platform</span>
                    </div>
                </div>
                <nav class="mt-4 space-y-2 px-3">
                    <a href="#" class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium rounded-lg text-slate-200 hover:bg-[#1b3650]">
                        <span class="w-6 h-6 rounded-full bg-[#2a9d8f] text-white text-xs flex items-center justify-center font-bold">D</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium rounded-lg text-slate-200 hover:bg-[#1b3650]">
                        <span class="w-6 h-6 rounded-full bg-[#2a9d8f] text-white text-xs flex items-center justify-center font-bold">L</span>
                        <span>Learn</span>
                    </a>
                    <a href="{{ route('quiz.index') }}" class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium rounded-lg bg-[#1e3f5a] text-white">
                        <span class="w-6 h-6 rounded-full bg-[#2a9d8f] text-white text-xs flex items-center justify-center font-bold">W</span>
                        <span>Wellbeing</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium rounded-lg text-slate-200 hover:bg-[#1b3650]">
                        <span class="w-6 h-6 rounded-full bg-[#2a9d8f] text-white text-xs flex items-center justify-center font-bold">C</span>
                        <span>Counselling</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium rounded-lg text-slate-200 hover:bg-[#1b3650]">
                        <span class="w-6 h-6 rounded-full bg-[#2a9d8f] text-white text-xs flex items-center justify-center font-bold">N</span>
                        <span>Notifications</span>
                    </a>
                </nav>
            </div>
            <div class="p-4 m-3 bg-[#1b3650] rounded-xl flex items-center space-x-3">
                <div class="w-9 h-9 bg-[#f4a261] rounded-full flex items-center justify-center font-bold text-white text-sm">S</div>
                <div>
                    <p class="text-sm font-semibold text-white leading-tight">Student</p>
                    <span class="text-xs text-slate-300">Secure account</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-y-auto">
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center shrink-0">
                <h2 class="text-lg font-bold text-[#12263a]">@yield('header_title', 'Wellbeing self check-in')</h2>
                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">Student portal</span>
            </header>
            <div class="p-8 flex-1">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>