<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Schedules &amp; Verification
        </h1>
    </x-slot>

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-[#0f2a4a]">Counsellor Verification Dashboard</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Review counsellor applications, inspect uploaded certificates, and approve or reject verification requests.</p>
        </div>

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 sm:p-8">
            <h3 class="text-lg font-bold text-[#0f2a4a] mb-5">Counsellor Applications</h3>

            @forelse ($counsellors as $counsellor)
                <div class="border border-slate-200 rounded-xl p-5 mb-5 last:mb-0">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <h4 class="font-bold text-base text-[#0f2a4a]">
                                {{ $counsellor->name }}
                            </h4>
                            <p class="text-slate-500 text-xs mt-0.5">
                                {{ $counsellor->email }}
                            </p>
                        </div>

                        @if ($counsellor->verification_status === 'pending')
                            <span class="bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full text-xs font-semibold">
                                Pending
                            </span>
                        @elseif ($counsellor->verification_status === 'verified')
                            <span class="bg-[#e6f5f0] text-[#1c9c8c] border border-[#cdece2] px-3 py-1 rounded-full text-xs font-semibold">
                                Verified
                            </span>
                        @elseif ($counsellor->verification_status === 'rejected')
                            <span class="bg-rose-50 text-rose-700 border border-rose-200 px-3 py-1 rounded-full text-xs font-semibold">
                                Rejected
                            </span>
                        @endif
                    </div>

                    <div class="mt-4">
                        @if ($counsellor->certificate_path)
                            <a href="{{ route('admin.counsellors.certificate', $counsellor->id) }}"
                               class="text-[#1c9c8c] font-semibold underline text-xs">
                                Download Certificate
                            </a>
                        @else
                            <p class="text-rose-500 text-xs">
                                No certificate uploaded.
                            </p>
                        @endif
                    </div>

                    @if ($counsellor->verification_status === 'pending')
                        <div class="mt-5 flex flex-wrap items-center gap-4">
                            {{-- APPROVE BUTTON --}}
                            <form
                                action="{{ route('admin.counsellors.approve', $counsellor->id) }}"
                                method="POST"
                            >
                                @csrf
                                <button
                                    type="submit"
                                    class="bg-[#1c9c8c] hover:bg-[#157a6e] text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-2xs transition cursor-pointer"
                                    onclick="return confirm('Are you sure you want to approve this counsellor?')"
                                >
                                    ✓ Approve Counsellor
                                </button>
                            </form>

                            {{-- REJECT FORM --}}
                            <form
                                action="{{ route('admin.counsellors.reject', $counsellor->id) }}"
                                method="POST"
                                class="flex flex-wrap items-center gap-2"
                            >
                                @csrf
                                <input
                                    type="text"
                                    name="rejection_reason"
                                    required
                                    maxlength="1000"
                                    placeholder="Enter rejection reason"
                                    class="border border-slate-300 rounded-xl px-3.5 py-2 text-xs"
                                >
                                <button
                                    type="submit"
                                    class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-2xs transition cursor-pointer"
                                    onclick="return confirm('Are you sure you want to reject this counsellor?')"
                                >
                                    ✕ Reject
                                </button>
                            </form>
                        </div>
                    @elseif ($counsellor->verification_status === 'rejected')
                        <div class="bg-rose-50 text-rose-700 p-3 rounded-xl mt-4 text-xs">
                            <strong>Rejection reason:</strong>
                            {{ $counsellor->rejection_reason }}
                        </div>
                    @elseif ($counsellor->verification_status === 'verified')
                        <div class="bg-[#e6f5f0] text-[#0f766e] p-3 rounded-xl mt-4 text-xs font-medium">
                            ✓ This counsellor has been successfully verified.
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-slate-500 text-center py-8 text-xs">
                    No counsellor applications found.
                </p>
            @endforelse
        </div>
    </div>
</x-app-layout>
