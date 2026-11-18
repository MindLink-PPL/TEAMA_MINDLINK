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
        @php
            $queueList = isset($counsellorQueue) && count($counsellorQueue) > 0
                ? collect($counsellorQueue)->values()->all()
                : [
                    [
                        'id' => null,
                        'code' => 'ST-204',
                        'concern' => 'Persistent anxiety',
                        'submitted_label' => 'Persistent anxiety • submitted 09:12',
                        'priority' => 'High priority',
                        'status' => 'Confirmed',
                        'scheduled_date' => 'Wednesday, 21 October 2026',
                        'scheduled_time' => '10:00',
                        'session_mode' => 'In person',
                        'header_title' => 'ST-204 • Appointment 10:00',
                        'student_name' => 'Alex Rivera (Student)',
                        'student_email' => 'student@mindlink.com',
                        'student_notes' => 'Persistent anxious thoughts affecting sleep and lecture attendance.',
                        'consent_verified' => true,
                        'consent_date' => '13 Oct 2026',
                        'wellbeing_score' => 21,
                        'wellbeing_mood' => 'Low',
                        'wellbeing_guidance' => 'High wellbeing reflection score; priority counselling recommended.',
                        'session_note' => '',
                        'note_status' => 'draft',
                        'support_action' => 'Priority Clinical Intake & Anxiety Support',
                        'notified' => false,
                    ],
                    [
                        'id' => null,
                        'code' => 'ST-187',
                        'concern' => 'Study pressure',
                        'submitted_label' => 'Study pressure • appointment 10:00',
                        'priority' => 'Scheduled',
                        'status' => 'Confirmed',
                        'scheduled_date' => 'Wednesday, 21 October 2026',
                        'scheduled_time' => '10:00',
                        'session_mode' => 'In person',
                        'header_title' => 'ST-187 • Appointment 10:00',
                        'student_name' => 'Student',
                        'student_email' => 'student@gmail.com',
                        'student_notes' => 'Experiencing difficulty concentrating ahead of mid-semester deadlines.',
                        'consent_verified' => true,
                        'consent_date' => '13 Oct 2026',
                        'wellbeing_score' => 14,
                        'wellbeing_mood' => 'Okay',
                        'wellbeing_guidance' => 'Moderate study pressure over the past two weeks.',
                        'session_note' => '',
                        'note_status' => 'draft',
                        'support_action' => 'Structured Study & Grounding Plan',
                        'notified' => false,
                    ],
                    [
                        'id' => null,
                        'code' => 'ST-221',
                        'concern' => 'Sleep difficulty',
                        'submitted_label' => 'Sleep difficulty • submitted yesterday',
                        'priority' => 'New',
                        'status' => 'Pending review',
                        'scheduled_date' => 'Thursday, 22 October 2026',
                        'scheduled_time' => '13:30',
                        'session_mode' => 'Online',
                        'header_title' => 'ST-221 • Appointment 13:30',
                        'student_name' => 'John Doe (Student)',
                        'student_email' => 'john@mindlink.com',
                        'student_notes' => 'Difficulty falling asleep before 2am on weeknights.',
                        'consent_verified' => true,
                        'consent_date' => '20 Oct 2026',
                        'wellbeing_score' => 11,
                        'wellbeing_mood' => 'Okay',
                        'wellbeing_guidance' => 'Sleep routine guidance and consultation requested.',
                        'session_note' => '',
                        'note_status' => 'none',
                        'support_action' => 'Sleep Hygiene & Routine Consultation',
                        'notified' => false,
                    ],
                    [
                        'id' => null,
                        'code' => 'ST-163',
                        'concern' => 'Follow-up requested by student',
                        'submitted_label' => 'Follow-up requested by student',
                        'priority' => 'Follow-up',
                        'status' => 'Confirmed',
                        'scheduled_date' => 'Wednesday, 21 October 2026',
                        'scheduled_time' => '15:00',
                        'session_mode' => 'In person',
                        'header_title' => 'ST-163 • Appointment 15:00',
                        'student_name' => 'Alex Rivera (Student)',
                        'student_email' => 'student@mindlink.com',
                        'student_notes' => 'Checking in on progress with the weekly workload boundary plan.',
                        'consent_verified' => true,
                        'consent_date' => '11 Oct 2026',
                        'wellbeing_score' => 21,
                        'wellbeing_mood' => 'Low',
                        'wellbeing_guidance' => 'Follow-up requested to evaluate academic pacing.',
                        'session_note' => '',
                        'note_status' => 'draft',
                        'support_action' => 'Follow-Up Wellbeing Review',
                        'notified' => false,
                    ],
                ];
        @endphp

        <div
            class="space-y-6"
            x-data="{
                queue: {{ \Illuminate\Support\Js::from($queueList) }},
                selectedCode: 'ST-187',
                filterStatus: 'All',
                selectedRequest: null,
                notes: '',
                modalError: '',
                get selectedItem() {
                    return this.queue.find(i => i.code === this.selectedCode) || this.queue[1] || this.queue[0];
                },
                openModal(req) {
                    this.selectedRequest = req;
                    this.notes = req.counsellor_notes || '';
                    this.modalError = '';
                },
                isFinalized() {
                    return ['Approved', 'Declined'].includes(this.selectedRequest?.status);
                },
                submitDecline() {
                    if (!this.notes.trim()) {
                        this.modalError = 'Please provide assessment or referral notes before declining a request.';
                        return;
                    }
                    this.$refs.declineForm.submit();
                }
            }"
        >
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Today's workload</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Prioritise requests and document sessions within the authorised workspace.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('counsellor.requests') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-[#0f2a4a] hover:bg-slate-50 transition shadow-2xs">
                        Manage Requests
                    </a>
                    <a href="{{ route('counsellor.records') }}" class="px-3.5 py-2 rounded-xl bg-[#1c9c8c] text-white text-xs font-bold hover:bg-[#157a6e] transition shadow-2xs">
                        Authorised Student Info &rarr;
                    </a>
                </div>
            </div>

            <!-- Metric Cards (PG-05 Exact Match) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <a href="{{ route('counsellor.requests') }}" class="p-6 rounded-2xl bg-[#fff3e6] border border-[#fde2c8] shadow-2xs hover:shadow-sm transition block">
                    <div class="text-xs font-medium text-slate-600">Pending requests</div>
                    <div class="text-4xl font-extrabold text-[#f58a42] mt-2">{{ $pendingRequestsCount ?? 7 }}</div>
                    <div class="text-xs text-slate-500 mt-4">{{ $highPriorityCount ?? 2 }} marked high priority</div>
                </a>

                <a href="{{ route('counsellor.schedule') }}" class="p-6 rounded-2xl bg-[#e6f5f0] border border-[#cdece2] shadow-2xs hover:shadow-sm transition block">
                    <div class="text-xs font-medium text-slate-600">Today's sessions</div>
                    <div class="text-4xl font-extrabold text-[#1c9c8c] mt-2">{{ $todaysSessionsCount ?? 4 }}</div>
                    <div class="text-xs text-slate-500 mt-4">Next session at 10:00</div>
                </a>

                <a href="{{ route('counsellor.requests', ['priority' => 'Follow-up']) }}" class="p-6 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:shadow-sm transition block">
                    <div class="text-xs font-medium text-slate-600">Follow-ups due</div>
                    <div class="text-4xl font-extrabold text-[#0f2a4a] mt-2">{{ $followUpsDueCount ?? 3 }}</div>
                    <div class="text-xs text-slate-500 mt-4">Complete before 16:00</div>
                </a>
            </div>

            <!-- Workspace Columns (PG-05 Exact Match + Interactive Queue & Session Note Submission) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Request Queue -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-bold text-[#0f2a4a]">Request queue</h3>
                            <a href="{{ route('counsellor.requests') }}" class="text-xs font-semibold text-[#1c9c8c] hover:underline">Newest first</a>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach ($queueList as $item)
                                @php
                                    $badgeStyle = match ($item['priority']) {
                                        'High priority' => 'bg-[#fff3e6] text-[#f58a42]',
                                        'Scheduled', 'Follow-up', 'Completed' => 'bg-[#e6f5f0] text-[#1c9c8c]',
                                        default => 'bg-[#f1f5f9] text-slate-600',
                                    };
                                @endphp
                                <div
                                    dusk="queue-item-{{ $item['code'] }}"
                                    @click="selectedCode = '{{ $item['code'] }}'"
                                    :class="selectedCode === '{{ $item['code'] }}' ? 'bg-[#f8fafc] -mx-3 px-3 rounded-xl ring-1 ring-[#1c9c8c]/30' : 'hover:bg-slate-50/70 -mx-3 px-3 rounded-xl'"
                                    class="py-3.5 flex items-center justify-between cursor-pointer transition"
                                >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-[#0f2a4a]">{{ $item['code'] }}</span>
                                            <span class="text-[11px] font-medium text-slate-400">&bull; {{ $item['student_name'] }}</span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $item['submitted_label'] }}</div>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $badgeStyle }}">
                                        {{ $item['priority'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Click any student request above to inspect authorised information and record session notes.</span>
                        <a href="{{ route('counsellor.requests') }}" class="font-bold text-[#1c9c8c] hover:underline shrink-0 ml-2">Full queue &rarr;</a>
                    </div>
                </div>

                <!-- Right: Session Note & Authorised Student Information Form -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                    <form method="POST" action="{{ route('counsellor.notes.store') }}">
                        @csrf
                        <input type="hidden" name="counselling_request_id" :value="selectedItem.id">
                        <input type="hidden" name="student_code" :value="selectedItem.code" value="ST-187">

                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold bg-[#fff3e6] text-[#f58a42] mb-2.5">
                            Authorised counsellor only
                        </span>

                        <h4 class="font-bold text-base text-[#0f2a4a]" x-text="selectedItem.header_title">
                            ST-187 &bull; Appointment 10:00
                        </h4>
                        <p class="text-xs text-slate-500 mt-1 mb-4">
                            Private notes are visible only to assigned counselling staff.
                        </p>

                        <!-- Authorised Student Information Summary Box -->
                        <div class="mb-4 p-3.5 rounded-xl bg-[#f8fafc] border border-slate-200/80 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#0f2a4a]" x-text="selectedItem.student_name">Student</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#e6f5f0] text-[#1c9c8c]" x-text="selectedItem.session_mode">
                                    In person
                                </span>
                            </div>
                            <div class="text-slate-600 flex items-center justify-between">
                                <span x-text="selectedItem.student_email">student@mindlink.com</span>
                                <span class="font-semibold text-[#0f2a4a]" x-text="selectedItem.priority">Scheduled</span>
                            </div>
                            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-200/70" x-text="selectedItem.student_notes">
                                Experiencing difficulty concentrating ahead of mid-semester deadlines.
                            </div>
                        </div>

                        <!-- Appropriate Support Action -->
                        <div class="mb-3">
                            <label for="workspace_support_action" class="block text-xs font-medium text-slate-700 mb-1">Support plan</label>
                            <input
                                id="workspace_support_action"
                                type="text"
                                name="support_action"
                                :value="selectedItem.support_action"
                                value="Structured Study & Grounding Plan"
                                class="w-full text-xs rounded-xl border border-slate-200 bg-[#f8fafc] text-[#0f2a4a] font-medium px-3 py-2 focus:ring-[#1c9c8c] focus:border-[#1c9c8c]"
                            >
                        </div>

                        <label for="workspace_session_note" class="block text-xs font-medium text-slate-700 mb-1.5">Session note</label>
                        <textarea
                            id="workspace_session_note"
                            name="session_note"
                            rows="4"
                            :value="selectedItem.session_note"
                            placeholder="Document observations and agreed next steps..."
                            class="w-full text-xs rounded-xl border border-slate-200 bg-[#f1f5f9] focus:bg-white focus:ring-[#1c9c8c] focus:border-[#1c9c8c] p-3 text-slate-700"
                            required
                        ></textarea>

                        <div class="mt-4 flex items-center gap-2.5">
                            <button
                                type="submit"
                                name="action_type"
                                value="draft"
                                class="flex-1 py-2.5 px-3 border border-[#1c9c8c] text-[#1c9c8c] font-bold text-xs rounded-xl hover:bg-[#e6f5f0]/50 transition"
                            >
                                Save draft
                            </button>
                            <button
                                type="submit"
                                name="action_type"
                                value="complete"
                                class="flex-1 py-2.5 px-3 bg-[#1c9c8c] text-white font-bold text-xs rounded-xl hover:bg-[#157a6e] transition shadow-2xs"
                            >
                                Complete and notify
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Time Availability Management (My Availability Slots) -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                <h3 class="text-lg font-bold text-[#0f2a4a] mb-1">My Availability Slots</h3>
                <p class="text-xs text-slate-500 mb-6">Set the dates and times when students can book appointments with you.</p>

                <form action="{{ route('counsellor.availability.store') }}" method="POST" class="border border-slate-200 rounded-2xl p-5 mb-6 bg-[#f8fafc]">
                    @csrf
                    <h4 class="font-semibold text-xs uppercase tracking-wider text-slate-700 mb-4">Add New Availability</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Available Date</label>
                            <input type="date" name="date" min="{{ date('Y-m-d') }}" value="{{ old('date') }}" required class="w-full border border-slate-300 rounded-xl p-3 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Start Time</label>
                            <input type="time" name="start_time" value="{{ old('start_time') }}" required class="w-full border border-slate-300 rounded-xl p-3 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">End Time</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}" required class="w-full border border-slate-300 rounded-xl p-3 text-xs bg-white">
                        </div>
                    </div>
                    <button type="submit" class="bg-[#0f2a4a] text-white text-xs font-bold px-5 py-2.5 rounded-xl mt-4 hover:bg-slate-800 transition cursor-pointer">
                        + Add Availability Slot
                    </button>
                </form>

                <div class="space-y-3">
                    @forelse ($availabilities ?? [] as $availability)
                        <div class="border border-slate-200 rounded-xl p-4 flex justify-between items-center bg-white">
                            <div>
                                <p class="font-semibold text-sm text-[#0f2a4a]">{{ \Carbon\Carbon::parse($availability->date)->format('d M Y') }}</p>
                                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($availability->time)->format('h:i A') }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="bg-[#e6f5f0] text-[#1c9c8c] border border-[#cdece2] px-3 py-1 rounded-full text-xs font-bold">Available</span>
                                <form action="{{ route('counsellor.availability.destroy', $availability->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-50 text-red-700 border border-red-200 text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-red-100 transition cursor-pointer">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic">No availability slots added yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Student Counselling Requests & Review/Approve/Decline Table -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="text-lg font-bold text-[#0f2a4a]">Student Counselling Requests</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="tab in ['All', 'Under Review', 'Approved', 'Declined']">
                            <button type="button"
                                @click="filterStatus = tab"
                                :class="filterStatus === tab ? 'bg-[#0f2a4a] text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                                class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                                x-text="tab">
                            </button>
                        </template>
                    </div>
                </div>

                @if(empty($requests) || $requests->isEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-10 text-center space-y-2 shadow-2xs">
                        <h3 class="text-base font-bold text-[#0f2a4a]">No requests found</h3>
                        <p class="text-xs text-slate-500">When students submit requests through the portal, they will appear here for review.</p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-[#f8fafc] border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider font-semibold">
                                        <th class="p-4"># ID</th>
                                        <th class="p-4">Counsellor</th>
                                        <th class="p-4">Concern Summary</th>
                                        <th class="p-4">Schedule</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                    @foreach($requests as $req)
                                        <tr x-show="filterStatus === 'All' || filterStatus === '{{ $req->status ?? 'Under Review' }}'" class="hover:bg-slate-50/60 transition">
                                            <td class="p-4 font-bold text-[#0f2a4a]">#{{ $req->request_id }}</td>
                                            <td class="p-4 font-semibold text-[#1c9c8c]">{{ $req->counsellor_name }}</td>
                                            <td class="p-4 max-w-xs">
                                                <p class="font-medium text-[#0f2a4a] truncate">{{ $req->concern }}</p>
                                                @if($req->goals)
                                                    <p class="text-xs text-slate-500 truncate">Goals: {{ $req->goals }}</p>
                                                @endif
                                                @if($req->counsellor_notes)
                                                    <p class="text-[11px] text-[#1c9c8c] truncate mt-0.5">Note: {{ $req->counsellor_notes }}</p>
                                                @endif
                                            </td>
                                            <td class="p-4 text-xs space-y-0.5">
                                                <p class="font-semibold text-[#0f2a4a]">{{ $req->appointment_date }} at {{ $req->appointment_time }}</p>
                                                <span class="inline-block bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-medium">{{ $req->appointment_mode }}</span>
                                            </td>
                                            <td class="p-4">
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold border
                                                    {{ $req->status === 'Approved' ? 'bg-teal-50 text-teal-700 border-teal-200' : '' }}
                                                    {{ $req->status === 'Declined' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                                    {{ !in_array($req->status, ['Approved', 'Declined']) ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}">
                                                    {{ $req->status ?? 'Under Review' }}
                                                </span>
                                            </td>
                                            <td class="p-4 text-right">
                                                <button type="button"
                                                    dusk="review-request-{{ $req->request_id }}"
                                                    @click="openModal({{ Js::from($req) }})"
                                                    class="bg-[#0f2a4a] hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-xl transition cursor-pointer">
                                                    {{ in_array($req->status, ['Approved', 'Declined']) ? 'View Details' : 'Review & Assess' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Student Concern Assessment Modal (Approve / Decline) -->
            <div x-show="selectedRequest"
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto py-8"
                style="display: none;">

                <div @click.away="selectedRequest = null" class="bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto my-auto">
                    <div class="flex justify-between items-start border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#1c9c8c]">Request #<span x-text="selectedRequest?.request_id"></span></span>
                            <h3 class="text-xl font-bold text-[#0f2a4a] mt-0.5">Student Concern Assessment</h3>
                        </div>
                        <button type="button" @click="selectedRequest = null" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                    </div>

                    <div class="space-y-4 text-sm text-slate-700">
                        <div class="bg-[#f8fafc] border border-slate-200/80 rounded-2xl p-4 space-y-3">
                            <div>
                                <span class="text-xs font-bold uppercase text-slate-400 block">Full Concern Description</span>
                                <p class="text-[#0f2a4a] mt-1 whitespace-pre-line" x-text="selectedRequest?.concern"></p>
                            </div>
                            <template x-if="selectedRequest?.goals">
                                <div class="pt-2 border-t border-slate-200/60">
                                    <span class="text-xs font-bold uppercase text-slate-400 block">Student Goals</span>
                                    <p class="text-slate-800 mt-1 whitespace-pre-line" x-text="selectedRequest?.goals"></p>
                                </div>
                            </template>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs bg-[#e6f5f0]/60 border border-[#cdece2] rounded-xl p-3.5">
                            <div>
                                <span class="text-slate-400 font-semibold uppercase block">Requested Slot</span>
                                <span class="font-bold text-[#0f2a4a]" x-text="selectedRequest?.appointment_date + ' • ' + selectedRequest?.appointment_time"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 font-semibold uppercase block">Session Mode</span>
                                <span class="font-bold text-[#1c9c8c]" x-text="selectedRequest?.appointment_mode"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                                Counsellor Assessment / Referral Notes
                                <span class="text-slate-400 normal-case font-normal">(Required if declining)</span>
                            </label>
                            <textarea rows="3"
                                x-model="notes"
                                @input="modalError = ''"
                                :disabled="isFinalized()"
                                class="w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-800 focus:ring-2 focus:ring-[#1c9c8c] disabled:bg-slate-100 disabled:text-slate-500"
                                placeholder="Write session preparation notes or referral instructions for the student..."></textarea>
                            <p x-show="modalError" x-text="modalError" class="text-xs font-semibold text-red-600 mt-2 bg-red-50 border border-red-200 px-3 py-2 rounded-lg"></p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <template x-if="isFinalized()">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-semibold text-slate-500">Status finalized as <strong x-text="selectedRequest?.status"></strong>.</span>
                                <button type="button" @click="selectedRequest = null" class="px-4 py-2 rounded-xl bg-[#0f2a4a] text-white font-semibold">Close</button>
                            </div>
                        </template>

                        <template x-if="!isFinalized()">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 mb-3">Determine Next Steps:</p>
                                <div class="flex items-center justify-end space-x-3">
                                    <form x-ref="declineForm" :action="'/counsellor/requests/' + selectedRequest?.request_id + '/status'" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="Declined">
                                        <input type="hidden" name="counsellor_notes" :value="notes">
                                        <button type="button" @click="submitDecline()" class="px-4 py-2.5 rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-xs font-bold transition cursor-pointer">
                                            Decline / Refer
                                        </button>
                                    </form>

                                    <form :action="'/counsellor/requests/' + selectedRequest?.request_id + '/status'" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="Approved">
                                        <input type="hidden" name="counsellor_notes" :value="notes">
                                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold shadow-2xs transition cursor-pointer">
                                            Approve &amp; Confirm Session
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </template>
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
                        <a href="{{ route('quiz.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#1c9c8c] hover:text-[#0f766e] transition">
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
                        <a href="{{ route('counselling.request') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#0f2a4a] hover:text-[#1c9c8c] transition">
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
                        <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#f58a42] hover:text-[#ea580c] transition">
                            <span>Browse articles</span>
                            <span>&gt;</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Grid: Role & Access Boundary (Left) & Upcoming appointment + Need urgent help? (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Column: Student Role & Permissions Card -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-[#0f2a4a]">Your Student Access &amp; Privacy</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#e6f5f0] text-[#1c9c8c]">
                                {{ Auth::user()->role?->name ?? 'Student' }} Role
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ Auth::user()->role?->description ?? 'Access student-specific features to manage your wellbeing and counselling activities securely.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                        @foreach (Auth::user()->permissions as $perm)
                            <div class="p-3 rounded-xl bg-[#f8fafc] border border-slate-200/80 text-xs">
                                <div class="font-bold text-[#0f2a4a]">{{ $perm->name }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $perm->module }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Right Column: Upcoming appointment + Need urgent help? -->
                <div class="lg:col-span-5 space-y-5">
                    <!-- Top Card: Upcoming appointment -->
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-[#0f2a4a]">Upcoming appointment</h3>
                            <a href="{{ route('counselling.request') }}" class="text-xs font-bold text-[#1c9c8c] hover:underline">Manage &rarr;</a>
                        </div>

                        @php
                            $apptStatus = $upcomingAppointment?->status ?? 'Confirmed';
                            $canCancelUpcoming = $upcomingAppointment && !in_array($apptStatus, ['Confirmed', 'Approved', 'Declined', 'Cancelled', 'Completed', 'Completed & Notified'], true);
                            $apptBadgeStyle = match (true) {
                                in_array($apptStatus, ['Declined', 'Cancelled'], true) => 'bg-red-50 text-red-600 border border-red-200',
                                in_array($apptStatus, ['Confirmed', 'Approved', 'Completed', 'Completed & Notified'], true) => 'bg-[#e6f5f0] text-[#1c9c8c]',
                                default => 'bg-amber-50 text-amber-700 border border-amber-200',
                            };
                        @endphp

                        <div class="mt-2.5 flex items-center justify-between gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $apptBadgeStyle }}">
                                {{ $apptStatus }}
                            </span>

                            @if ($canCancelUpcoming)
                                <form action="{{ route('counselling.cancel', $upcomingAppointment) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        dusk="dashboard-cancel-request-{{ $upcomingAppointment->id }}"
                                        class="px-3 py-1 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold text-xs transition cursor-pointer"
                                    >
                                        Cancel Request
                                    </button>
                                </form>
                            @endif
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
