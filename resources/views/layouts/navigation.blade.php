@php
    $user = Auth::user();
    $roleSlug = $user->role?->slug ?? 'user';
    $isAdmin = $user->isAdmin();
    $isCounsellor = $user->isCounsellor();
@endphp

<!-- Desktop & Mobile Sidebar Container -->
<aside
    x-data="{ mobileOpen: false }"
    @toggle-sidebar.window="mobileOpen = !mobileOpen"
    class="relative"
>
    <!-- Mobile Backdrop -->
    <div
        x-show="mobileOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileOpen = false"
        class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden"
        style="display: none;"
    ></div>

    <!-- Sidebar Bar -->
    <div
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed top-0 bottom-0 left-0 w-64 bg-[#0f2a4a] text-slate-200 z-50 flex flex-col justify-between transition-transform duration-300 ease-in-out border-r border-[#163a63]"
    >
        <!-- Top Section: Brand & Navigation -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Brand Header -->
            <div class="p-6 pb-5 flex items-center justify-between border-b border-[#1b3d66]">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#0d9488] flex items-center justify-center text-white font-bold text-lg shadow-inner">
                        M
                    </div>
                    <div>
                        <div class="text-white font-bold text-lg tracking-tight leading-tight">MindLink</div>
                        <div class="text-[11px] text-slate-400 font-medium">Support platform</div>
                    </div>
                </a>
                <button @click="mobileOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto">
                @if ($isAdmin)
                    <!-- Admin Navigation (Matching PG-06) -->
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-[#1b3d66] text-white shadow-sm' : 'text-slate-300 hover:bg-[#153457] hover:text-white' }}"
                    >
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ request()->routeIs('dashboard') ? 'bg-[#14b8a6] text-white' : 'bg-[#1c4068] text-teal-300' }}">
                            O
                        </div>
                        <span>Overview</span>
                    </a>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-[#1b3d66] text-white shadow-sm' : 'text-slate-300 hover:bg-[#153457] hover:text-white' }}"
                    >
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ request()->routeIs('admin.users.*') ? 'bg-[#14b8a6] text-white' : 'bg-[#1c4068] text-teal-300' }}">
                            U
                        </div>
                        <span>Users & Roles</span>
                    </a>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            C
                        </div>
                        <span>Content</span>
                    </div>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            S
                        </div>
                        <span>Schedules</span>
                    </div>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            R
                        </div>
                        <span>Reports</span>
                    </div>

                @elseif ($isCounsellor)
                    <!-- Counsellor Navigation (Matching PG-05) -->
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-[#1b3d66] text-white shadow-sm' : 'text-slate-300 hover:bg-[#153457] hover:text-white' }}"
                    >
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-[#14b8a6] text-white">
                            W
                        </div>
                        <span>Workspace</span>
                    </a>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            R
                        </div>
                        <span>Requests</span>
                    </div>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            S
                        </div>
                        <span>Schedule</span>
                    </div>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            N
                        </div>
                        <span>Records</span>
                    </div>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            F
                        </div>
                        <span>Referrals</span>
                    </div>

                @else
                    <!-- Student Navigation (Matching PG-01 / PG-03 / PG-04) -->
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-[#1b3d66] text-white shadow-sm' : 'text-slate-300 hover:bg-[#153457] hover:text-white' }}"
                    >
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-[#14b8a6] text-white">
                            D
                        </div>
                        <span>Dashboard</span>
                    </a>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            L
                        </div>
                        <span>Learn</span>
                    </div>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            W
                        </div>
                        <span>Wellbeing</span>
                    </div>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            C
                        </div>
                        <span>Counselling</span>
                    </div>

                    <div class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="w-7 h-7 rounded-full bg-[#1c4068] text-slate-400 flex items-center justify-center text-xs font-bold">
                            N
                        </div>
                        <span>Notifications</span>
                    </div>
                @endif
            </nav>
        </div>

        <!-- Bottom User Profile Card (Matching Figma "Admin / Secure account") -->
        <div class="p-4 border-t border-[#1b3d66]" x-data="{ userMenu: false }">
            <div class="relative">
                <button
                    @click="userMenu = !userMenu"
                    class="w-full p-2.5 rounded-xl bg-[#143355] hover:bg-[#1b3d66] border border-[#1f4773] transition flex items-center justify-between text-left group"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full {{ $isAdmin ? 'bg-[#ea580c]' : ($isCounsellor ? 'bg-[#0d9488]' : 'bg-[#f97316]') }} text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-sm">
                            {{ $isAdmin ? 'A' : ($isCounsellor ? 'C' : 'S') }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-white text-xs font-bold truncate">
                                {{ $user->role?->name ?? 'User' }}
                            </div>
                            <div class="text-[11px] text-slate-400 truncate">Secure account</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Popup -->
                <div
                    x-show="userMenu"
                    @click.away="userMenu = false"
                    x-transition
                    class="absolute bottom-full left-0 w-full mb-2 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50 text-slate-700 text-xs"
                    style="display: none;"
                >
                    <div class="px-3 py-2 border-b border-slate-100">
                        <div class="font-bold text-slate-900 truncate">{{ $user->name }}</div>
                        <div class="text-slate-500 truncate text-[11px]">{{ $user->email }}</div>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Profile Settings</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-rose-600 hover:bg-rose-50 transition text-left">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
