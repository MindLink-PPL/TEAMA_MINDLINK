<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Counselling Request
        </h1>
    </x-slot>

    <div
        class="space-y-6"
        x-data="{
            step: 3,
            counsellor: 'Alisa • Student wellbeing',
            concern: 'Academic pressure and anxiety',
            scheduledDate: 'Wednesday, 21 October 2026',
            shortDate: '21 October 2026',
            scheduledTime: '10:00',
            sessionMode: 'In person',
            consentAccepted: true,
            notes: ''
        }"
    >
        <!-- Section Heading -->
        <div>
            <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Request private support</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Complete the four steps before submitting your request.
            </p>
        </div>

        <!-- 4-Step Stepper Indicator (Matching PG-04) -->
        <div class="grid grid-cols-4 max-w-2xl gap-4 pt-1">
            <!-- Step 1: Concern -->
            <button type="button" @click="step = 1" class="flex flex-col items-center group focus:outline-none">
                <div
                    :class="step === 1 ? 'bg-[#0f2a4a] text-white' : 'bg-[#1c9c8c] text-white'"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition shadow-2xs"
                >
                    1
                </div>
                <span class="text-xs font-semibold text-[#0f2a4a] mt-2">Concern</span>
            </button>

            <!-- Step 2: Consent -->
            <button type="button" @click="step = 2" class="flex flex-col items-center group focus:outline-none">
                <div
                    :class="step === 2 ? 'bg-[#0f2a4a] text-white' : (step > 2 ? 'bg-[#1c9c8c] text-white' : 'bg-[#dbe2ea] text-slate-500')"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition shadow-2xs"
                >
                    2
                </div>
                <span class="text-xs font-semibold text-[#0f2a4a] mt-2">Consent</span>
            </button>

            <!-- Step 3: Schedule -->
            <button type="button" @click="step = 3" class="flex flex-col items-center group focus:outline-none">
                <div
                    :class="step === 3 ? 'bg-[#0f2a4a] text-white' : (step > 3 ? 'bg-[#1c9c8c] text-white' : 'bg-[#dbe2ea] text-slate-500')"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition shadow-2xs"
                >
                    3
                </div>
                <span class="text-xs font-semibold text-[#0f2a4a] mt-2">Schedule</span>
            </button>

            <!-- Step 4: Review -->
            <button type="button" @click="step = 4" class="flex flex-col items-center group focus:outline-none">
                <div
                    :class="step === 4 ? 'bg-[#0f2a4a] text-white' : 'bg-[#dbe2ea] text-slate-500'"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition"
                >
                    4
                </div>
                <span class="text-xs font-semibold text-[#0f2a4a] mt-2">Review</span>
            </button>
        </div>

        <!-- Main Two-Column Layout (Matching PG-04) -->
        <form method="POST" action="{{ route('student.counselling.store') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            @csrf
            <input type="hidden" name="counsellor_name" :value="counsellor" value="Alisa • Student wellbeing">
            <input type="hidden" name="concern" :value="concern" value="Academic pressure and anxiety">
            <input type="hidden" name="scheduled_date" :value="scheduledDate" value="Wednesday, 21 October 2026">
            <input type="hidden" name="scheduled_time" :value="scheduledTime" value="10:00">
            <input type="hidden" name="session_mode" :value="sessionMode" value="In person">
            <input type="hidden" name="consent" value="1">

            <!-- Left Card: Choose an available time / Stepper Controls -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-5">
                <h3 class="text-lg font-bold text-[#0f2a4a]">Choose an available time</h3>

                <!-- Counsellor Selection -->
                <div>
                    <label for="counsellor_select" class="block text-xs font-medium text-slate-500 mb-2">Counsellor</label>
                    <select
                        id="counsellor_select"
                        x-model="counsellor"
                        class="w-full px-4 py-3 rounded-xl bg-[#f1f5f9] border-0 text-xs sm:text-sm font-medium text-[#0f2a4a] focus:ring-2 focus:ring-[#1c9c8c]"
                    >
                        @foreach ($counsellors as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Concern Selection (Shown when editing Step 1 or accessible anytime) -->
                <div x-show="step === 1" style="display: none;" class="pt-2 border-t border-slate-100">
                    <label for="concern_select" class="block text-xs font-medium text-slate-500 mb-2">Primary concern</label>
                    <select
                        id="concern_select"
                        x-model="concern"
                        class="w-full px-4 py-2.5 rounded-xl bg-[#f8fafc] border border-slate-200 text-xs sm:text-sm font-medium text-[#0f2a4a] focus:ring-2 focus:ring-[#1c9c8c]"
                    >
                        @foreach ($concerns as $item)
                            <option value="{{ $item }}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Consent Notice (Shown when on Step 2) -->
                <div x-show="step === 2" style="display: none;" class="p-4 rounded-xl bg-[#e6f5f0]/60 border border-[#cdece2] text-xs text-slate-700 space-y-2">
                    <div class="font-bold text-[#0f2a4a]">Confidentiality &amp; Informed Consent</div>
                    <p>Your counselling request and session notes are strictly confidential and accessible only to your assigned counsellor.</p>
                </div>

                <!-- Date Heading -->
                <div class="pt-1">
                    <div class="text-sm font-bold text-[#0f2a4a]" x-text="scheduledDate">
                        Wednesday, 21 October 2026
                    </div>

                    <!-- Time Slot Buttons (09:00, 10:00, 13:30, 15:00) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                        @foreach ($timeSlots as $slot)
                            <button
                                type="button"
                                @click="scheduledTime = '{{ $slot }}'"
                                :class="scheduledTime === '{{ $slot }}'
                                    ? 'bg-[#1c9c8c] text-white border-[#1c9c8c]'
                                    : 'bg-white text-[#1c9c8c] border-[#1c9c8c] hover:bg-[#e6f5f0]/50'"
                                class="py-2.5 px-4 rounded-xl border text-xs font-bold text-center transition focus:outline-none {{ $slot === '10:00' ? 'bg-[#1c9c8c] text-white border-[#1c9c8c]' : 'bg-white text-[#1c9c8c] border-[#1c9c8c]' }}"
                            >
                                {{ $slot }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Session Mode Pill (In person / Online) -->
                <div class="flex items-center gap-2 pt-1">
                    <button
                        type="button"
                        @click="sessionMode = 'In person'"
                        :class="sessionMode === 'In person' ? 'bg-[#e6f5f0] text-[#1c9c8c]' : 'bg-slate-100 text-slate-500'"
                        class="px-3.5 py-1 rounded-full text-xs font-semibold transition"
                    >
                        In person
                    </button>
                    <button
                        type="button"
                        @click="sessionMode = 'Online'"
                        :class="sessionMode === 'Online' ? 'bg-[#e6f5f0] text-[#1c9c8c]' : 'bg-slate-100 text-slate-500'"
                        class="px-3.5 py-1 rounded-full text-xs font-semibold transition"
                    >
                        Online
                    </button>
                </div>

                <!-- Continue / Submit Action -->
                <div class="pt-2 flex items-center gap-3">
                    <button
                        type="submit"
                        class="px-6 py-2.5 rounded-xl bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold transition shadow-2xs"
                    >
                        Continue
                    </button>
                </div>
            </div>

            <!-- Right Card: Your request summary (Matching PG-04) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-[#0f2a4a]">Your request</h3>

                    <div class="mt-4 space-y-3.5">
                        <div>
                            <div class="text-xs text-slate-400">Concern</div>
                            <div class="text-sm font-bold text-[#0f2a4a] mt-0.5" x-text="concern">
                                Academic pressure and anxiety
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400">Consent</div>
                            <div class="text-sm font-bold text-[#0f2a4a] mt-0.5">
                                Accepted on 13 October 2026
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400">Schedule</div>
                            <div class="text-sm font-bold text-[#0f2a4a] mt-0.5" x-text="shortDate + ', ' + scheduledTime">
                                21 October 2026, 10:00
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400">Status</div>
                            <div class="text-sm font-bold text-[#0f2a4a] mt-0.5">
                                Draft &ndash; not yet submitted
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    <div class="px-4 py-3 rounded-xl bg-[#fff3e6] text-[#f58a42] text-xs font-semibold">
                        Review the request before submission.
                    </div>
                </div>
            </div>
        </form>

        <!-- Scheduled Appointments List -->
        @if ($appointments->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                <h3 class="text-base font-bold text-[#0f2a4a]">Your scheduled appointments</h3>
                <p class="text-xs text-slate-500 mt-0.5 mb-4">Private appointments booked through the student portal.</p>

                <div class="divide-y divide-slate-100">
                    @foreach ($appointments as $appt)
                        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <div class="text-sm font-bold text-[#0f2a4a]">
                                    {{ $appt->scheduled_date }} &bull; {{ $appt->scheduled_time }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $appt->counsellor_name }} &bull; {{ $appt->concern }} ({{ $appt->session_mode }})
                                </div>
                            </div>
                            <span class="inline-flex items-center self-start sm:self-center px-3 py-1 rounded-full text-xs font-semibold bg-[#e6f5f0] text-[#1c9c8c]">
                                {{ $appt->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

