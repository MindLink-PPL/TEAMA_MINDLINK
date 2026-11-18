<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Counselling Requests
        </h1>
    </x-slot>

    <div
        class="space-y-6"
        x-data="{
            selectedRequest: null,
            notes: '',
            modalError: '',
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

                        <!-- Authorised Student Snapshot + Approve/Decline Trigger -->
                        <div class="p-4 rounded-xl bg-[#f8fafc] border border-slate-200/80 min-w-[240px] space-y-2 text-xs">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-[#1c9c8c]">
                                Authorised Student Info
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Role:</span>
                                <strong class="text-[#0f2a4a]">{{ $student?->role?->name ?? 'Student' }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Session Mode:</span>
                                <strong class="text-[#0f2a4a]">{{ $req->session_mode }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Status:</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $req->status === 'Declined' ? 'bg-red-50 text-red-600 border border-red-200' : (in_array($req->status, ['Approved', 'Confirmed', 'Completed', 'Completed & Notified']) ? 'bg-[#e6f5f0] text-[#1c9c8c]' : 'bg-amber-50 text-amber-700') }}">
                                    {{ $req->status }}
                                </span>
                            </div>
                            <div class="pt-2 border-t border-slate-200/70">
                                <button
                                    type="button"
                                    dusk="review-request-{{ $req->request_id }}"
                                    @click="openModal({{ Js::from($req) }})"
                                    class="w-full py-2 px-3 rounded-xl bg-[#0f2a4a] hover:bg-slate-800 text-white text-xs font-bold transition cursor-pointer"
                                >
                                    {{ in_array($req->status, ['Approved', 'Declined']) ? 'View Details' : 'Review & Assess' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200/90 p-8 text-center">
                    <div class="text-base font-bold text-[#0f2a4a]">No counselling requests match your filter</div>
                    <p class="text-xs text-slate-500 mt-1">Reset your filter to view all active student counselling requests.</p>
                </div>
            @endforelse
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
</x-app-layout>
