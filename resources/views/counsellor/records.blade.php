<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Authorised Student Records
        </h1>
    </x-slot>

    <div class="space-y-6">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Authorised student information &amp; clinical notes</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Access consented student wellbeing reflections, mood history, and confidential session notes to provide appropriate support.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-[#fff3e6] text-[#f58a42]">
                    Authorised counsellor only
                </span>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('counsellor.records') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-3xl">
            <div class="flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search authorised students by name, email, or student code (ST-187, ST-204)..."
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-[#1c9c8c] focus:border-[#1c9c8c] transition shadow-2xs"
                >
            </div>
            <button
                type="submit"
                class="px-6 py-2.5 bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-2xs shrink-0"
            >
                Search Records
            </button>
        </form>

        <!-- Authorised Student Cards -->
        <div class="space-y-6">
            @forelse ($students as $student)
                @php
                    $latestRequest = $student->counsellingRequests->first();
                    $studentCode = $latestRequest?->display_code ?? ('ST-' . (180 + $student->id));
                @endphp

                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-6">
                    <!-- Student Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-[#0f2a4a] text-white font-extrabold text-sm flex items-center justify-center">
                                {{ $studentCode }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-[#0f2a4a]">{{ $student->name }}</h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#e6f5f0] text-[#1c9c8c]">
                                        Authorised student record
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $student->email }} &bull; Student ID: <strong>{{ $studentCode }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="px-3.5 py-2 rounded-xl bg-[#f8fafc] border border-slate-200/80 text-xs">
                                <span class="text-slate-500">Assigned Role:</span>
                                <strong class="text-[#0f2a4a] ml-1">{{ $student->role?->name ?? 'Student' }}</strong>
                            </div>
                            <div class="px-3.5 py-2 rounded-xl bg-[#e6f5f0] border border-[#cdece2] text-xs">
                                <span class="text-slate-600">Support Requests:</span>
                                <strong class="text-[#1c9c8c] ml-1">{{ $student->counsellingRequests->count() }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Two-Column Authorised Data & Session Note Documentation -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- Left: Request & Support History -->
                        <div class="lg:col-span-7 space-y-4">
                            <h4 class="text-sm font-bold text-[#0f2a4a]">Authorised Support &amp; Session History</h4>

                            <!-- Student's Counselling Requests -->
                            <div class="space-y-2.5">
                                @forelse ($student->counsellingRequests as $req)
                                    <div class="p-4 rounded-xl bg-white border border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                                        <div>
                                            <div class="font-bold text-[#0f2a4a]">
                                                {{ $req->display_code }} &bull; {{ $req->concern }}
                                            </div>
                                            <div class="text-slate-500 mt-0.5">
                                                {{ $req->scheduled_date }} at {{ $req->scheduled_time }} ({{ $req->session_mode }}) &bull;
                                                Support: <strong>{{ $req->support_action ?: 'Individual Counselling' }}</strong>
                                            </div>
                                            @if ($req->session_note)
                                                <div class="mt-2 p-2.5 rounded-lg bg-[#fff3e6]/60 border border-[#fde2c8] text-slate-700">
                                                    <strong>Confidential Note ({{ ucfirst($req->note_status) }}):</strong> {{ $req->session_note }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full font-semibold {{ $req->status === 'Declined' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-[#e6f5f0] text-[#1c9c8c]' }} self-start sm:self-center shrink-0">
                                            {{ $req->status }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-500">No counselling requests recorded for this student yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Right: Record Confidential Session Note & Support Plan -->
                        <div class="lg:col-span-5 bg-[#f8fafc] rounded-2xl border border-slate-200/80 p-5">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#fff3e6] text-[#f58a42] mb-2">
                                Confidential clinical documentation
                            </span>
                            <h4 class="text-sm font-bold text-[#0f2a4a]">
                                Document support for {{ $student->name }} ({{ $studentCode }})
                            </h4>
                            <p class="text-[11px] text-slate-500 mt-0.5 mb-3">
                                Notes remain strictly within the counsellor workspace and are never exposed to administrators.
                            </p>

                            <form method="POST" action="{{ route('counsellor.notes.store') }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="counselling_request_id" value="{{ $latestRequest?->id }}">
                                <input type="hidden" name="student_code" value="{{ $studentCode }}">

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Recommended Support Plan</label>
                                    <input
                                        type="text"
                                        name="support_action"
                                        value="{{ $latestRequest?->support_action ?? 'Individual Counselling & Wellbeing Support' }}"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-[#0f2a4a]"
                                    >
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Confidential Session Note</label>
                                    <textarea
                                        name="session_note"
                                        rows="3"
                                        placeholder="Document observations and agreed next steps..."
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs text-slate-700"
                                        required
                                    >{{ $latestRequest?->session_note }}</textarea>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <button
                                        type="submit"
                                        name="action_type"
                                        value="draft"
                                        class="flex-1 py-2 px-3 rounded-xl border border-[#1c9c8c] text-[#1c9c8c] text-xs font-bold hover:bg-[#e6f5f0]/50 transition"
                                    >
                                        Save draft
                                    </button>
                                    <button
                                        type="submit"
                                        name="action_type"
                                        value="complete"
                                        class="flex-1 py-2 px-3 rounded-xl bg-[#1c9c8c] text-white text-xs font-bold hover:bg-[#157a6e] transition"
                                    >
                                        Complete and notify
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200/90 p-8 text-center">
                    <div class="text-base font-bold text-[#0f2a4a]">No matching student records found</div>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
