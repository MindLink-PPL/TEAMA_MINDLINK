<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Referrals &amp; Follow-Ups
        </h1>
    </x-slot>

    <div class="space-y-6">
        <!-- Section Header -->
        <div>
            <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Student support referrals &amp; follow-up plans</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Coordinate appropriate academic, wellbeing, or specialist support referrals for authorised students.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Create New Support Referral Form -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold bg-[#e6f5f0] text-[#1c9c8c] mb-2">
                    Appropriate Student Support
                </span>
                <h3 class="text-base font-bold text-[#0f2a4a]">Create support referral</h3>
                <p class="text-xs text-slate-500 mt-0.5 mb-5">
                    Assign a structured support plan or specialist referral based on authorised student information.
                </p>

                <form method="POST" action="{{ route('counsellor.referrals.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="referral_student" class="block text-xs font-medium text-slate-600 mb-1.5">Authorised Student</label>
                        <select
                            id="referral_student"
                            name="user_id"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8fafc] border border-slate-200 text-xs font-semibold text-[#0f2a4a] focus:ring-[#1c9c8c]"
                            required
                        >
                            @foreach ($students as $student)
                                @php
                                    $code = $student->counsellingRequests->first()?->display_code ?? ('ST-' . (180 + $student->id));
                                @endphp
                                <option value="{{ $student->id }}">{{ $code }} &bull; {{ $student->name }} ({{ $student->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="referral_type" class="block text-xs font-medium text-slate-600 mb-1.5">Support / Referral Pathway</label>
                        <select
                            id="referral_type"
                            name="referral_type"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8fafc] border border-slate-200 text-xs font-semibold text-[#0f2a4a] focus:ring-[#1c9c8c]"
                            required
                        >
                            @foreach ($referralTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="referral_urgency" class="block text-xs font-medium text-slate-600 mb-1.5">Priority Level</label>
                        <select
                            id="referral_urgency"
                            name="urgency"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8fafc] border border-slate-200 text-xs font-semibold text-[#0f2a4a] focus:ring-[#1c9c8c]"
                            required
                        >
                            <option value="High priority">High priority</option>
                            <option value="Standard" selected>Standard</option>
                            <option value="Follow-up">Follow-up</option>
                        </select>
                    </div>

                    <div>
                        <label for="referral_reason" class="block text-xs font-medium text-slate-600 mb-1.5">Clinical / Wellbeing Rationale</label>
                        <textarea
                            id="referral_reason"
                            name="reason"
                            rows="2"
                            placeholder="Summarise authorised wellbeing indicators or student concerns..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8fafc] border border-slate-200 text-xs text-slate-700 focus:ring-[#1c9c8c]"
                            required
                        ></textarea>
                    </div>

                    <div>
                        <label for="referral_plan" class="block text-xs font-medium text-slate-600 mb-1.5">Agreed Support Plan &amp; Next Steps</label>
                        <textarea
                            id="referral_plan"
                            name="support_plan"
                            rows="3"
                            placeholder="Document recommended accommodations, check-in cadence, or follow-up actions..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8fafc] border border-slate-200 text-xs text-slate-700 focus:ring-[#1c9c8c]"
                            required
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2.5 px-4 rounded-xl bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold transition shadow-2xs"
                    >
                        Save Support Referral
                    </button>
                </form>
            </div>

            <!-- Right: Active Referrals & Follow-Ups List -->
            <div class="lg:col-span-7 space-y-4">
                @forelse ($referrals as $referral)
                    @php
                        $urgencyBadge = match ($referral->urgency) {
                            'High priority' => 'bg-[#fff3e6] text-[#f58a42]',
                            'Follow-up' => 'bg-[#e6f5f0] text-[#1c9c8c]',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-extrabold text-[#0f2a4a]">{{ $referral->student_code }}</span>
                                <span class="text-xs font-bold text-slate-600">&bull; {{ $referral->student?->name ?? 'Authorised Student' }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $urgencyBadge }}">
                                    {{ $referral->urgency }}
                                </span>
                            </div>
                            <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-[#e6f5f0] text-[#1c9c8c]">
                                {{ $referral->status }}
                            </span>
                        </div>

                        <div class="text-sm font-bold text-[#0f2a4a]">
                            {{ $referral->referral_type }}
                        </div>

                        <div class="text-xs text-slate-600">
                            <strong>Rationale:</strong> {{ $referral->reason }}
                        </div>

                        <div class="p-3.5 rounded-xl bg-[#f8fafc] border border-slate-200/80 text-xs text-slate-700">
                            <strong class="text-[#0f2a4a]">Support Plan:</strong> {{ $referral->support_plan }}
                        </div>

                        <form method="POST" action="{{ route('counsellor.referrals.update', $referral) }}" class="pt-2 flex items-center justify-between gap-3">
                            @csrf
                            @method('PATCH')
                            <span class="text-[11px] text-slate-400">
                                Assigned by {{ $referral->counsellor?->name ?? 'Counselling Team' }}
                            </span>
                            <div class="flex items-center gap-2">
                                <select
                                    name="status"
                                    class="px-3 py-1.5 rounded-xl border border-slate-200 bg-[#f8fafc] text-xs font-semibold text-[#0f2a4a]"
                                >
                                    @foreach (['Active', 'Follow-up due', 'Completed'] as $statusOpt)
                                        <option value="{{ $statusOpt }}" @selected($referral->status === $statusOpt)>{{ $statusOpt }}</option>
                                    @endforeach
                                </select>
                                <button
                                    type="submit"
                                    class="px-3.5 py-1.5 rounded-xl bg-[#0f2a4a] hover:bg-[#1b3d66] text-white text-xs font-bold transition"
                                >
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-8 text-center">
                        <div class="text-base font-bold text-[#0f2a4a]">No active referrals</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
