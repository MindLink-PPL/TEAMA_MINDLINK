<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Notifications
        </h1>
    </x-slot>

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Private updates</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Confidential updates regarding your counselling appointments and wellbeing check-ins.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
            @forelse ($appointments as $appt)
                <div class="p-4 rounded-xl bg-[#f8fafc] border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#e6f5f0] text-[#1c9c8c] mb-1.5">
                            Appointment {{ $appt->status }}
                        </span>
                        <h3 class="text-sm font-bold text-[#0f2a4a]">
                            Session with {{ $appt->counsellor_name }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Scheduled for {{ $appt->scheduled_date }} at {{ $appt->scheduled_time }} ({{ $appt->session_mode }})
                        </p>
                    </div>
                    <a href="{{ route('student.counselling') }}" class="text-xs font-bold text-[#1c9c8c] hover:underline shrink-0">
                        View schedule &rarr;
                    </a>
                </div>
            @empty
                <div class="p-4 rounded-xl bg-[#f8fafc] border border-slate-200/80">
                    <h3 class="text-sm font-bold text-[#0f2a4a]">Appointment Confirmed</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Wednesday, 21 October 2026 &bull; 10:00–11:00 with Alisa, Counsellor.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>

