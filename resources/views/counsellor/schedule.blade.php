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
                    Manage your time availability slots, scheduled student appointments, and session modes.
                </p>
            </div>
            <div>
                <a href="{{ route('counsellor.requests') }}" class="px-4 py-2 rounded-xl bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold transition shadow-2xs inline-block">
                    Open Request Queue &rarr;
                </a>
            </div>
        </div>

        <!-- My Availability Slots Management -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
            <h3 class="text-lg font-bold text-[#0f2a4a] mb-1">My Availability Slots</h3>
            <p class="text-xs text-slate-500 mb-6">Set the dates and times when students can book appointments with you.</p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-3.5 rounded-xl text-xs mb-4">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
