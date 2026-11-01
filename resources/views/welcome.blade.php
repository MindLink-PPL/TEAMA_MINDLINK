<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MindLink - Support Platform</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-[#f4f7fa] text-slate-800 antialiased flex flex-col justify-between">
        <!-- Top Navigation -->
        <header class="bg-white border-b border-slate-200/80 px-6 sm:px-8 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#0d9488] flex items-center justify-center text-white font-bold text-lg shadow-inner">
                        M
                    </div>
                    <div>
                        <div class="text-[#0f2a4a] font-bold text-lg tracking-tight leading-tight">MindLink</div>
                        <div class="text-[11px] text-slate-500 font-medium">Support platform</div>
                    </div>
                </div>

                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-bold text-xs sm:text-sm transition shadow-2xs">
                            Open Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:text-slate-900 transition">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-bold text-xs sm:text-sm transition shadow-2xs">
                            Register
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Hero & Portal Presentation -->
        <main class="flex-1 max-w-5xl w-full mx-auto px-6 py-12 flex flex-col justify-center">
            <div class="text-center space-y-5 max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-[#ebf8f3] text-[#059669] border border-[#bbf7d0]">
                    <span class="w-2 h-2 rounded-full bg-[#059669]"></span>
                    MindLink Platform Architecture
                </span>

                <h1 class="text-4xl sm:text-5xl font-extrabold text-[#0f2a4a] tracking-tight leading-tight">
                    Secure Student Support &amp; Role-Based Administration
                </h1>

                <p class="text-base text-slate-600 leading-relaxed max-w-2xl mx-auto">
                    A privacy-first platform connecting Students, Counsellors, and Administrators. Role-based permissions protect sensitive student information while granting administrators high-level service oversight.
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-bold text-sm transition shadow-sm">
                            Go to {{ Auth::user()->portal_name }}
                        </a>
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#0f2a4a] hover:bg-[#16375c] text-white font-bold text-sm transition shadow-sm">
                                Open User &amp; Role Management
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-bold text-sm transition shadow-sm">
                            Sign in to Portal
                        </a>
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-bold text-sm transition shadow-2xs">
                            Create Student Account
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Pre-Seeded Accounts Grid (Styled in MindLink Figma Palette) -->
            <div class="mt-14 bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-2xs">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-full bg-[#ebf8f3] text-[#059669] flex items-center justify-center font-bold text-xs">
                        &bull;
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Pre-seeded Institutional Accounts for Quick Testing</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <!-- Admin Card -->
                    <div class="p-4 rounded-xl bg-[#fef4ea] border border-[#fed7aa]">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-bold text-[#9a3412]">Administrator (PG-06)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#ea580c] text-white font-bold">Admin Portal</span>
                        </div>
                        <p class="text-slate-600 mb-3">Service overview, privacy boundaries, role &amp; user management.</p>
                        <div class="bg-white/80 p-2.5 rounded-lg border border-[#fed7aa] font-mono text-slate-800 space-y-0.5">
                            <div>Email: <strong class="text-[#ea580c]">admin@mindlink.com</strong></div>
                            <div>Password: <strong>password</strong></div>
                        </div>
                    </div>

                    <!-- Counsellor Card -->
                    <div class="p-4 rounded-xl bg-[#ebf8f3] border border-[#bbf7d0]">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-bold text-[#065f46]">Counsellor (PG-05)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#0d9488] text-white font-bold">Counsellor Portal</span>
                        </div>
                        <p class="text-slate-600 mb-3">Workload, appointment request queue, confidential notes.</p>
                        <div class="bg-white/80 p-2.5 rounded-lg border border-[#bbf7d0] font-mono text-slate-800 space-y-0.5">
                            <div>Email: <strong class="text-[#0d9488]">counsellor@mindlink.com</strong></div>
                            <div>Password: <strong>password</strong></div>
                        </div>
                    </div>

                    <!-- Student Card -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-bold text-slate-700">Student (PG-01)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 text-slate-700 font-bold">Student Portal</span>
                        </div>
                        <p class="text-slate-600 mb-3">Wellbeing check-ins, mental health literacy, support requests.</p>
                        <div class="bg-white/80 p-2.5 rounded-lg border border-slate-200 font-mono text-slate-800 space-y-0.5">
                            <div>Email: <strong class="text-slate-900">student@mindlink.com</strong></div>
                            <div>Password: <strong>password</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200/80 px-6 py-4 text-center text-xs text-slate-500">
            MindLink &bull; Support platform &copy; {{ date('Y') }}. Designed for confidential student mental health and administrative oversight.
        </footer>
    </body>
</html>
