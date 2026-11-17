<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Counsellor Schedule
        </h1>
    </x-slot>

    <div class="space-y-6">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Today's sessions &amp; timetable</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Manage scheduled student appointments, session modes, and clinical preparation.
                </p>
            </div>
            <div>
                <a href="{{ route('counsellor.requests') }}" class="px-4 py-2 rounded-xl bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold transition shadow-2xs inline-block">
                    Open Request Queue &rarr;
                </a>
            </div>
        </div>

        <!-- Time Slot Overview Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($timeSlots as $slot)
                @php
                    $slotAppt = $appointments->firstWhere('scheduled_time', $slot);
                @endphp
                <div class="p-5 rounded-2xl border {{ $slotAppt ? 'bg-[#e6f5f0] border-[#cdece2]' : 'bg-white border-slate-200/90' }} shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-extrabold text-[#0f2a4a]">{{ $slot }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $slotAppt ? 'bg-[#1c9c8c] text-white' : 'bg-slate-100 text-slate-500' }}">
                                {{ $slotAppt ? $slotAppt->session_mode : 'Available' }}
                            </span>
                        </div>
                        @if ($slotAppt)
                            <div class="mt-3 text-sm font-bold text-[#0f2a4a]">
                                {{ $slotAppt->display_code }} &bull; {{ $slotAppt->user?->name ?? 'Student' }}
                            </div>
                            <p class="text-xs text-slate-600 mt-1">{{ $slotAppt->concern }}</p>
                        @else
                            <div class="mt-3 text-xs text-slate-500">
                                Open slot for urgent student intake or follow-up.
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200/60 text-[11px] font-semibold text-[#1c9c8c]">
                        {{ $slotAppt ? 'Wednesday, 21 October 2026' : 'Ready for booking' }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Detailed Scheduled Sessions -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
            <h3 class="text-base font-bold text-[#0f2a4a] mb-1">Scheduled counselling appointments</h3>
            <p class="text-xs text-slate-500 mb-5">Authorised session schedule with quick support &amp; timing controls.</p>

            <div class="divide-y divide-slate-100">
                @foreach ($appointments as $appt)
                    <div class="py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm font-extrabold text-[#0f2a4a]">{{ $appt->display_code }}</span>
                                <span class="text-xs font-bold text-[#1c9c8c]">{{ $appt->scheduled_date }} &bull; {{ $appt->scheduled_time }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#e6f5f0] text-[#1c9c8c]">
                                    {{ $appt->session_mode }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-600">
                                <strong>Student:</strong> {{ $appt->user?->name ?? 'Student' }} &bull;
                                <strong>Concern:</strong> {{ $appt->concern }} &bull;
                                <strong>Support Plan:</strong> {{ $appt->support_action ?: 'Individual Counselling' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('counsellor.records', ['search' => $appt->display_code]) }}" class="px-3.5 py-2 rounded-xl border border-[#1c9c8c] text-[#1c9c8c] text-xs font-bold hover:bg-[#e6f5f0]/50 transition">
                                View Student Record
                            </a>
                            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-[#1c9c8c] text-white text-xs font-bold hover:bg-[#157a6e] transition">
                                Document Session
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
