<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Wellbeing Check In
        </h1>
    </x-slot>

    @php
        $questionsJson = collect($questions)->values()->toJson();
    @endphp

    <div
        class="space-y-6"
        x-data="{
            currentStep: 6,
            totalSteps: 10,
            questions: {{ $questionsJson }},
            answers: {
                1: 1,
                2: 1,
                3: 1,
                4: 1,
                5: 1,
                6: 1,
                7: 1,
                8: 1,
                9: 1,
                10: 1
            },
            get currentQuestion() {
                return this.questions[this.currentStep - 1] || this.questions[5];
            }
        }"
    >
        <!-- Section Title -->
        <div>
            <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">A short reflection</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">This check-in is informational and is not a diagnosis.</p>
        </div>

        <!-- Progress Bar (Matching PG-03) -->
        <div>
            <div class="w-full h-3 bg-[#dbe2ea] rounded-full overflow-hidden">
                <div
                    class="h-full bg-[#1c9c8c] rounded-full transition-all duration-300"
                    :style="'width: ' + ((currentStep / totalSteps) * 100) + '%'"
                    style="width: 60%;"
                ></div>
            </div>
            <div class="text-xs font-medium text-slate-500 mt-2" x-text="currentStep + ' of ' + totalSteps">
                6 of 10
            </div>
        </div>

        <!-- Quiz Card (Matching PG-03) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-2xs max-w-3xl">
            <form method="POST" action="{{ route('student.wellbeing.store') }}" x-ref="checkinForm">
                @csrf

                <!-- Hidden inputs for all 10 reflection questions -->
                @foreach ($questions as $num => $q)
                    <input type="hidden" name="answers[{{ $num }}]" :value="answers[{{ $num }}]" value="1">
                @endforeach

                <div class="text-xs font-semibold text-[#1c9c8c]" x-text="currentQuestion.prompt">
                    Over the past two weeks
                </div>

                <h3
                    class="text-lg sm:text-xl font-bold text-[#0f2a4a] mt-1.5 leading-snug"
                    x-text="currentQuestion.question"
                >
                    How often have you found it difficult to concentrate on study or daily activities?
                </h3>

                <!-- Answer Options -->
                <div class="mt-5 space-y-3">
                    @foreach ($options as $score => $label)
                        <button
                            type="button"
                            @click="answers[currentStep] = {{ $score }}"
                            :class="answers[currentStep] === {{ $score }}
                                ? 'border-[#1c9c8c] bg-[#e6f5f0] text-[#0f2a4a] font-medium'
                                : 'border-slate-200 bg-white text-slate-700 hover:border-[#1c9c8c]/50'"
                            class="w-full px-4 py-3.5 rounded-xl border text-left text-xs sm:text-sm flex items-center gap-3 transition focus:outline-none {{ $score === 1 ? 'border-[#1c9c8c] bg-[#e6f5f0] text-[#0f2a4a] font-medium' : 'border-slate-200 bg-white text-slate-700' }}"
                        >
                            <span
                                :class="answers[currentStep] === {{ $score }}
                                    ? 'bg-[#1c9c8c] border-[#1c9c8c]'
                                    : 'bg-white border-slate-400'"
                                class="w-4 h-4 rounded-full border shrink-0 transition {{ $score === 1 ? 'bg-[#1c9c8c] border-[#1c9c8c]' : 'bg-white border-slate-400' }}"
                            ></span>
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Navigation Buttons (Back & Continue / Submit) -->
                <div class="mt-6 flex items-center justify-between gap-3">
                    <button
                        type="button"
                        @click="if (currentStep > 1) { currentStep--; }"
                        :disabled="currentStep === 1"
                        :class="currentStep === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#e6f5f0]/60'"
                        class="px-5 py-2.5 rounded-xl border border-[#1c9c8c] text-[#1c9c8c] text-xs font-bold transition"
                    >
                        Back
                    </button>

                    <div class="flex items-center gap-2.5">
                        <button
                            type="button"
                            x-show="currentStep < totalSteps"
                            @click="currentStep++"
                            class="px-6 py-2.5 rounded-xl bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold transition shadow-2xs"
                        >
                            Continue
                        </button>

                        <button
                            type="submit"
                            x-show="currentStep === totalSteps"
                            style="display: none;"
                            class="px-6 py-2.5 rounded-xl bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs font-bold transition shadow-2xs"
                        >
                            Submit check-in
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Recent Reflections History (if any completed) -->
        @if ($recentCheckins->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs max-w-3xl">
                <h3 class="text-base font-bold text-[#0f2a4a]">Recent private reflections</h3>
                <p class="text-xs text-slate-500 mt-0.5 mb-4">Only you can view your self-reflection check-in history.</p>

                <div class="divide-y divide-slate-100">
                    @foreach ($recentCheckins as $checkin)
                        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <div class="text-xs font-bold text-[#0f2a4a]">
                                    Completed {{ $checkin->created_at->format('j F Y, H:i') }}
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    {{ $checkin->guidance }}
                                </p>
                            </div>
                            <span class="inline-flex items-center self-start sm:self-center px-3 py-1 rounded-full text-xs font-semibold bg-[#e6f5f0] text-[#1c9c8c] shrink-0">
                                Score: {{ $checkin->total_score }} / 30
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

