<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
            {{ Auth::user()->isAdmin() ? 'Administrative Dashboard' : (Auth::user()->isCounsellor() ? 'Counsellor Workspace' : 'Student Dashboard') }}
        </h1>
    </x-slot>

    @if (Auth::user()->isAdmin())
        <!-- ========================================== -->
        <!-- PG-06: ADMINISTRATIVE DASHBOARD (Figma Match) -->
        <!-- ========================================== -->
        <div class="space-y-6">
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Service overview</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Monitor access, content, and aggregate service activity.</p>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#ecfdf5] text-[#059669] border border-[#a7f3d0]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#059669]"></span>
                        Reporting period &bull; {{ date('F') }}
                    </span>
                </div>
            </div>

            <!-- Top Row: 3 Metric Cards (Figma exact match) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Card 1: Registered students / users (Mint Green) -->
                <div class="p-6 rounded-2xl bg-[#ebf8f3] border border-[#bbf7d0] shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-600">Registered users</div>
                        <div class="text-4xl font-extrabold text-[#059669] mt-2">
                            {{ \App\Models\User::count() }}
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-4 flex items-center justify-between">
                        <span>+{{ \App\Models\User::where('created_at', '>=', now()->subDays(30))->count() }} this month</span>
                        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-[#059669] hover:underline">Manage &rarr;</a>
                    </div>
                </div>

                <!-- Card 2: Counselling requests / Role activities (Peach / Orange) -->
                <div class="p-6 rounded-2xl bg-[#fef4ea] border border-[#fed7aa] shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-600">Role & access status</div>
                        <div class="text-4xl font-extrabold text-[#ea580c] mt-2">
                            {{ \App\Models\Role::count() }} Active Roles
                        </div>
                    </div>
                    <div class="text-xs text-[#9a3412] mt-4 flex items-center justify-between">
                        <span>{{ \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->count() }} Admins &bull; {{ \App\Models\User::whereHas('role', fn($q) => $q->where('slug', '!=', 'admin'))->count() }} Managed</span>
                        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-[#ea580c] hover:underline">Inspect &rarr;</a>
                    </div>
                </div>

                <!-- Card 3: Sessions completed / Security clearance (White) -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-600">Permissions active</div>
                        <div class="text-4xl font-extrabold text-slate-900 mt-2">
                            {{ \App\Models\Permission::count() }}
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-4 flex items-center justify-between">
                        <span>100% RBAC security verified</span>
                        <span class="text-emerald-600 font-semibold">&bull; Active</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Grid: Weekly request activity (Left) & Content/Privacy (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Weekly request activity (Bar Chart) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-slate-900">Weekly request activity</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Aggregate counts; no personal counselling notes are shown.</p>
                    </div>

                    <!-- Visual Chart Container (Figma exact replicate) -->
                    <div class="bg-[#f8fafc] rounded-xl p-6 sm:p-8 flex items-end justify-around h-64 border border-slate-100">
                        <!-- Bar 1: W1 -->
                        <div class="flex flex-col items-center gap-2 h-full justify-end group">
                            <span class="text-xs font-bold text-slate-600">8</span>
                            <div class="w-10 sm:w-12 bg-[#14b8a6] hover:bg-[#0d9488] transition-all rounded-lg" style="height: 40%;"></div>
                            <span class="text-xs font-semibold text-slate-500 mt-1">W1</span>
                        </div>

                        <!-- Bar 2: W2 -->
                        <div class="flex flex-col items-center gap-2 h-full justify-end group">
                            <span class="text-xs font-bold text-slate-600">12</span>
                            <div class="w-10 sm:w-12 bg-[#14b8a6] hover:bg-[#0d9488] transition-all rounded-lg" style="height: 60%;"></div>
                            <span class="text-xs font-semibold text-slate-500 mt-1">W2</span>
                        </div>

                        <!-- Bar 3: W3 -->
                        <div class="flex flex-col items-center gap-2 h-full justify-end group">
                            <span class="text-xs font-bold text-slate-600">10</span>
                            <div class="w-10 sm:w-12 bg-[#14b8a6] hover:bg-[#0d9488] transition-all rounded-lg" style="height: 50%;"></div>
                            <span class="text-xs font-semibold text-slate-500 mt-1">W3</span>
                        </div>

                        <!-- Bar 4: W4 -->
                        <div class="flex flex-col items-center gap-2 h-full justify-end group">
                            <span class="text-xs font-bold text-slate-600">17</span>
                            <div class="w-10 sm:w-12 bg-[#14b8a6] hover:bg-[#0d9488] transition-all rounded-lg" style="height: 85%;"></div>
                            <span class="text-xs font-semibold text-slate-500 mt-1">W4</span>
                        </div>

                        <!-- Bar 5: W5 -->
                        <div class="flex flex-col items-center gap-2 h-full justify-end group">
                            <span class="text-xs font-bold text-slate-600">14</span>
                            <div class="w-10 sm:w-12 bg-[#14b8a6] hover:bg-[#0d9488] transition-all rounded-lg" style="height: 70%;"></div>
                            <span class="text-xs font-semibold text-slate-500 mt-1">W5</span>
                        </div>
                    </div>

                    <!-- Role Quick Actions -->
                    <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-slate-500">
                            Looking to modify user security privileges? Access the dedicated directory:
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-[#0d9488] hover:bg-[#0f766e] text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center gap-2">
                            <span>Open User & Role Management</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Right Column: Content status + Privacy Boundary -->
                <div class="space-y-6">
                    <!-- Content status card -->
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                        <h3 class="text-base font-bold text-slate-900 mb-4">Content status</h3>

                        <div class="space-y-3.5 text-sm">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <span class="text-slate-600">Published resources</span>
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#ecfdf5] text-[#059669] font-bold text-xs">
                                    24
                                </span>
                            </div>

                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <span class="text-slate-600">Draft resources</span>
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs">
                                    5
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Awaiting review</span>
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#fef4ea] text-[#ea580c] font-bold text-xs">
                                    3
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- PRIVACY BOUNDARY CARD (Figma Exact Match) -->
                    <div class="bg-[#0f2a4a] text-white rounded-2xl p-6 shadow-md border border-[#1b3d66]">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#f59e0b] block">
                            Privacy Boundary
                        </span>
                        <h4 class="text-base font-bold text-white mt-2 leading-snug">
                            Administration sees service data, not counselling notes.
                        </h4>
                        <p class="text-xs text-slate-300 mt-3 leading-relaxed">
                            Role-based permissions protect sensitive student information.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    @elseif (Auth::user()->isCounsellor())
        <!-- ========================================== -->
        <!-- PG-05: COUNSELLOR WORKSPACE (Figma Match)  -->
        <!-- ========================================== -->
        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Today's workload</h2>
                <p class="text-sm text-slate-500 mt-0.5">Prioritise requests and document sessions within the authorised workspace.</p>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-6 rounded-2xl bg-[#fef4ea] border border-[#fed7aa] shadow-2xs">
                    <div class="text-xs font-medium text-slate-600">Pending requests</div>
                    <div class="text-4xl font-extrabold text-[#ea580c] mt-2">7</div>
                    <div class="text-xs text-[#9a3412] mt-4">2 marked high priority</div>
                </div>

                <div class="p-6 rounded-2xl bg-[#ebf8f3] border border-[#bbf7d0] shadow-2xs">
                    <div class="text-xs font-medium text-slate-600">Today's sessions</div>
                    <div class="text-4xl font-extrabold text-[#059669] mt-2">4</div>
                    <div class="text-xs text-slate-500 mt-4">Next session at 10:00</div>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                    <div class="text-xs font-medium text-slate-600">Follow-ups due</div>
                    <div class="text-4xl font-extrabold text-slate-900 mt-2">3</div>
                    <div class="text-xs text-slate-500 mt-4">Complete before 16:00</div>
                </div>
            </div>

            <!-- Workspace Columns -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Request Queue -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-900">Request queue</h3>
                        <span class="text-xs font-medium text-[#0d9488]">Newest first</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <div class="py-3.5 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-sm text-slate-900">ST-204</div>
                                <div class="text-xs text-slate-500">Persistent anxiety &bull; submitted 09:12</div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-[#fef4ea] text-[#ea580c]">High priority</span>
                        </div>

                        <div class="py-3.5 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-sm text-slate-900">ST-187</div>
                                <div class="text-xs text-slate-500">Study pressure &bull; appointment 10:00</div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-[#ecfdf5] text-[#059669]">Scheduled</span>
                        </div>

                        <div class="py-3.5 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-sm text-slate-900">ST-221</div>
                                <div class="text-xs text-slate-500">Sleep difficulty &bull; submitted yesterday</div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">New</span>
                        </div>

                        <div class="py-3.5 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-sm text-slate-900">ST-163</div>
                                <div class="text-xs text-slate-500">Follow-up requested by student</div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-[#ecfdf5] text-[#059669]">Follow-up</span>
                        </div>
                    </div>
                </div>

                <!-- Session Note Form -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#fef4ea] text-[#ea580c] mb-2">
                        Authorised counsellor only
                    </span>
                    <h4 class="font-bold text-base text-slate-900">ST-187 &bull; Appointment 10:00</h4>
                    <p class="text-xs text-slate-500 mt-1 mb-4">Private notes are visible only to assigned counselling staff.</p>

                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Session note</label>
                    <textarea rows="4" placeholder="Document observations and agreed next steps..." class="w-full text-xs rounded-xl border-slate-200 bg-[#f8fafc] focus:ring-[#0d9488] focus:border-[#0d9488] p-3"></textarea>

                    <div class="mt-4 flex items-center gap-2">
                        <button type="button" class="flex-1 py-2 px-3 border border-[#0d9488] text-[#0d9488] font-bold text-xs rounded-xl hover:bg-teal-50 transition">
                            Save draft
                        </button>
                        <button type="button" class="flex-1 py-2 px-3 bg-[#0d9488] text-white font-bold text-xs rounded-xl hover:bg-[#0f766e] transition">
                            Complete and notify
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- ========================================== -->
        <!-- PG-01: STUDENT DASHBOARD (Figma Match)     -->
        <!-- ========================================== -->
        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Good morning, {{ Auth::user()->name }}</h2>
                <p class="text-sm text-slate-500 mt-1">Choose a private next step that feels manageable today.</p>
            </div>

            <!-- Student Action Cards (PG-01 Exact Match) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Card 1: Check in -->
                <div class="p-6 rounded-2xl bg-[#e6f5f0] border border-[#cdece2] shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-base text-[#0f2a4a]">Check in</h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Record how you feel and receive non-diagnostic guidance.
                        </p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('student.wellbeing') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#1c9c8c] hover:text-[#0f766e] transition">
                            <span>Start check-in</span>
                            <span>&gt;</span>
                        </a>
                    </div>
                </div>

                <!-- Card 2: Request counselling -->
                <div class="p-6 rounded-2xl bg-[#f8fafc] border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-base text-[#0f2a4a]">Request counselling</h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Submit a private request and choose an available time.
                        </p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('student.counselling') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#0f2a4a] hover:text-[#1c9c8c] transition">
                            <span>Request support</span>
                            <span>&gt;</span>
                        </a>
                    </div>
                </div>

                <!-- Card 3: Learn about mental health -->
                <div class="p-6 rounded-2xl bg-[#fff3e6] border border-[#fde2c8] shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-base text-[#0f2a4a]">Learn about mental health</h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Read practical articles reviewed by authorised staff.
                        </p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('student.literacy') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#f58a42] hover:text-[#ea580c] transition">
                            <span>Browse articles</span>
                            <span>&gt;</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Grid: How are you feeling today? (Left) & Upcoming appointment + Need urgent help? (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Column: Wellbeing reflection prompt -->
                <div
                    class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs flex flex-col justify-between"
                    x-data="{ selectedMood: '{{ $latestMood ?? 'Good' }}' }"
                >
                    <form method="POST" action="{{ route('student.mood.store') }}">
                        @csrf
                        <input type="hidden" name="mood" :value="selectedMood">

                        <h3 class="text-lg font-bold text-[#0f2a4a]">How are you feeling today?</h3>
                        <p class="text-xs text-slate-500 mt-1 mb-7">Your answer stays private and can be changed later.</p>

                        <div class="grid grid-cols-5 gap-3 sm:gap-5 max-w-lg">
                            <!-- Very low -->
                            <button
                                type="button"
                                @click="selectedMood = 'Very low'"
                                class="flex flex-col items-center group focus:outline-none"
                            >
                                <span
                                    :class="selectedMood === 'Very low' ? 'ring-4 ring-[#d9534f]/30 scale-110' : 'opacity-90 hover:scale-105'"
                                    class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-[#d9534f] transition transform block shadow-2xs"
                                ></span>
                                <span class="text-[11px] font-medium text-slate-500 mt-2.5">Very low</span>
                            </button>

                            <!-- Low -->
                            <button
                                type="button"
                                @click="selectedMood = 'Low'"
                                class="flex flex-col items-center group focus:outline-none"
                            >
                                <span
                                    :class="selectedMood === 'Low' ? 'ring-4 ring-[#f58a42]/30 scale-110' : 'opacity-90 hover:scale-105'"
                                    class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-[#f58a42] transition transform block shadow-2xs"
                                ></span>
                                <span class="text-[11px] font-medium text-slate-500 mt-2.5">Low</span>
                            </button>

                            <!-- Okay -->
                            <button
                                type="button"
                                @click="selectedMood = 'Okay'"
                                class="flex flex-col items-center group focus:outline-none"
                            >
                                <span
                                    :class="selectedMood === 'Okay' ? 'ring-4 ring-[#e5b84b]/30 scale-110' : 'opacity-90 hover:scale-105'"
                                    class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-[#e5b84b] transition transform block shadow-2xs"
                                ></span>
                                <span class="text-[11px] font-medium text-slate-500 mt-2.5">Okay</span>
                            </button>

                            <!-- Good -->
                            <button
                                type="button"
                                @click="selectedMood = 'Good'"
                                class="flex flex-col items-center group focus:outline-none"
                            >
                                <span
                                    :class="selectedMood === 'Good' ? 'ring-4 ring-[#489e64]/30 scale-110' : 'opacity-90 hover:scale-105'"
                                    class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-[#489e64] transition transform block shadow-2xs"
                                ></span>
                                <span class="text-[11px] font-medium text-slate-500 mt-2.5">Good</span>
                            </button>

                            <!-- Great -->
                            <button
                                type="button"
                                @click="selectedMood = 'Great'"
                                class="flex flex-col items-center group focus:outline-none"
                            >
                                <span
                                    :class="selectedMood === 'Great' ? 'ring-4 ring-[#1c9c8c]/30 scale-110' : 'opacity-90 hover:scale-105'"
                                    class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-[#1c9c8c] transition transform block shadow-2xs"
                                ></span>
                                <span class="text-[11px] font-medium text-slate-500 mt-2.5">Great</span>
                            </button>
                        </div>

                        <div class="mt-8">
                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold rounded-xl transition shadow-2xs"
                            >
                                Save response
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Upcoming appointment + Need urgent help? -->
                <div class="lg:col-span-5 space-y-5">
                    <!-- Top Card: Upcoming appointment -->
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                        <h3 class="text-lg font-bold text-[#0f2a4a]">Upcoming appointment</h3>

                        <div class="mt-2.5">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#e6f5f0] text-[#1c9c8c]">
                                {{ $upcomingAppointment?->status ?? 'Confirmed' }}
                            </span>
                        </div>

                        <div class="mt-3 text-sm font-bold text-[#0f2a4a]">
                            {{ $upcomingAppointment?->scheduled_date ?? 'Wednesday, 21 October 2026' }}
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            @if ($upcomingAppointment)
                                {{ $upcomingAppointment->scheduled_time }} &bull; {{ explode('•', $upcomingAppointment->counsellor_name)[0] ? trim(explode('•', $upcomingAppointment->counsellor_name)[0]) . ', Counsellor' : $upcomingAppointment->counsellor_name }}
                            @else
                                10:00–11:00 &bull; Alisa, Counsellor
                            @endif
                        </div>
                    </div>

                    <!-- Bottom Card: Need urgent help? -->
                    <div class="bg-[#133453] text-white rounded-2xl p-6 shadow-sm">
                        <h3 class="text-base font-bold text-white">Need urgent help?</h3>
                        <p class="text-xs text-slate-200 mt-2 leading-relaxed">
                            MindLink is not an emergency service. Contact the listed campus or emergency support channel if you may be in immediate danger.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
