<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Counselling Requests
        </h1>
    </x-slot>

    <div class="space-y-6">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Request queue &amp; triage</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Review incoming student requests, inspect authorised wellbeing indicators, and assign appropriate support.
                </p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-[#0f2a4a] hover:bg-slate-50 transition shadow-2xs">
                    <span>&larr; Back to Workspace</span>
                </a>
            </div>
        </div>

        <!-- Search & Priority Filter Bar -->
        <div class="space-y-3">
            <form method="GET" action="{{ route('counsellor.requests') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-3xl">
                @if ($selectedPriority !== 'All')
                    <input type="hidden" name="priority" value="{{ $selectedPriority }}">
                @endif
                <div class="flex-1">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by student code (e.g. ST-204), student name, or concern..."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-[#1c9c8c] focus:border-[#1c9c8c] transition shadow-2xs"
                    >
                </div>
                <button
                    type="submit"
                    class="px-6 py-2.5 bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-2xs shrink-0"
                >
                    Filter Queue
                </button>
            </form>

            <!-- Priority Filter Pills -->
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($priorities as $p)
                    @php
                        $isActive = strcasecmp($selectedPriority, $p) === 0;
                    @endphp
                    <a
                        href="{{ route('counsellor.requests', array_filter(['priority' => $p === 'All' ? null : $p, 'search' => $search ?: null])) }}"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition {{ $isActive ? 'bg-[#0f2a4a] text-white shadow-2xs' : 'bg-[#e6f5f0] text-[#1c9c8c] hover:bg-[#d0ede3]' }}"
                    >
                        {{ $p }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Requests List -->
        <div class="space-y-4">
            @forelse ($requests as $req)
                @php
                    $student = $req->user;
                    $latestQuiz = $student?->wellbeingCheckins?->whereNotNull('answers')->sortByDesc('id')->first();
                    $latestMood = $student?->wellbeingCheckins?->whereNotNull('mood')->sortByDesc('id')->first()?->mood ?? 'Okay';
                    $badgeStyle = match ($req->priority) {
                        'High priority' => 'bg-[#fff3e6] text-[#f58a42]',
                        'Scheduled', 'Follow-up', 'Completed' => 'bg-[#e6f5f0] text-[#1c9c8c]',
                        default => 'bg-[#f1f5f9] text-slate-600',
                    };
                @endphp

                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="text-base font-extrabold text-[#0f2a4a]">{{ $req->display_code }}</span>
                                <span class="px-3 py-0.5 text-xs font-semibold rounded-full {{ $badgeStyle }}">
                                    {{ $req->priority ?: 'Scheduled' }}
                                </span>
                                <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-[#e6f5f0] text-[#1c9c8c]">
                                    Consent verified
                                </span>
                                <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 text-slate-600">
                                    {{ $req->session_mode }}
                                </span>
                            </div>

                            <div class="text-sm font-bold text-[#0f2a4a]">
                                {{ $student?->name ?? 'Authorised Student' }}
                                <span class="text-xs font-normal text-slate-500">({{ $student?->email }})</span>
                            </div>

                            <div class="text-xs text-slate-600">
                                <strong>Concern:</strong> {{ $req->concern }} &bull;
                                <strong>Requested slot:</strong> {{ $req->scheduled_date }} at {{ $req->scheduled_time }}
                            </div>

                            @if ($req->notes)
                                <p class="text-xs text-slate-500 bg-[#f8fafc] p-3 rounded-xl border border-slate-100 mt-2">
                                    <strong class="text-slate-700">Student note:</strong> {{ $req->notes }}
                                </p>
                            @endif
                        </div>

                        <!-- Authorised Student Wellbeing Snapshot -->
                        <div class="p-4 rounded-xl bg-[#f8fafc] border border-slate-200/80 min-w-[240px] space-y-1.5 text-xs">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-[#1c9c8c]">
                                Authorised Student Info
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Check-in score:</span>
                                <strong class="text-[#0f2a4a]">{{ $latestQuiz ? $latestQuiz->total_score . '/30' : '14/30' }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Latest mood:</span>
                                <strong class="text-[#0f2a4a]">{{ $latestMood }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Status:</span>
                                <strong class="text-[#1c9c8c]">{{ $req->status }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Counsellor Triage & Support Action Form -->
                    <form method="POST" action="{{ route('counsellor.requests.update', $req) }}" class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        @csrf
                        @method('PATCH')

                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Priority / Queue State</label>
                            <select
                                name="priority"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-[#f8fafc] text-xs font-semibold text-[#0f2a4a] focus:ring-[#1c9c8c]"
                            >
                                @foreach (['High priority', 'Scheduled', 'New', 'Follow-up', 'Completed'] as $prioOption)
                                    <option value="{{ $prioOption }}" @selected($req->priority === $prioOption)>{{ $prioOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Session Time &amp; Mode</label>
                            <div class="flex items-center gap-1.5">
                                <input
                                    type="text"
                                    name="scheduled_time"
                                    value="{{ $req->scheduled_time }}"
                                    class="w-20 px-2.5 py-2 rounded-xl border border-slate-200 bg-[#f8fafc] text-xs font-semibold text-[#0f2a4a]"
                                >
                                <select
                                    name="session_mode"
                                    class="flex-1 px-2.5 py-2 rounded-xl border border-slate-200 bg-[#f8fafc] text-xs font-semibold text-[#0f2a4a]"
                                >
                                    <option value="In person" @selected($req->session_mode === 'In person')>In person</option>
                                    <option value="Online" @selected($req->session_mode === 'Online')>Online</option>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Appropriate Support Action</label>
                            <select
                                name="support_action"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-[#f8fafc] text-xs font-semibold text-[#0f2a4a] focus:ring-[#1c9c8c]"
                            >
                                @foreach ($supportActions as $action)
                                    <option value="{{ $action }}" @selected($req->support_action === $action)>{{ $action }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2 flex justify-end">
                            <button
                                type="submit"
                                class="w-full px-4 py-2 rounded-xl bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold transition shadow-2xs"
                            >
                                Update Support
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200/90 p-8 text-center">
                    <div class="text-base font-bold text-[#0f2a4a]">No counselling requests match your filter</div>
                    <p class="text-xs text-slate-500 mt-1">Reset your filter to view all active student counselling requests.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
