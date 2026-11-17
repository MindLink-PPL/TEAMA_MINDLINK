@extends('layouts.mindlink')

@section('header_title', 'Wellbeing self check-in: Quiz Session')

@section('content')
<!-- STATE 1: Quiz Details Briefing View (Kept in your preferred layout) -->
<div id="briefingView" class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <div class="mb-8 border-b pb-6">
        <span class="text-xs font-semibold text-teal-600 uppercase">Stress Handling</span>
        <h2 class="text-2xl font-bold text-gray-900 mt-1">{{ $quiz->title }}</h2>
        <p class="text-sm text-gray-500 mt-1">Diagnostic Assessment • Created {{ $quiz->created_at->format('d M Y, H:i') }}</p>
    </div>

    <div class="prose text-sm text-gray-700 mb-8 space-y-4">
        <p>{{ $quiz->description }}</p>
        <div class="bg-teal-50 border border-teal-100 p-4 rounded-lg text-teal-900">
            <strong>Instructions:</strong> Read each statement carefully and select the option that best reflects your experience over the past several weeks. Your responses are strictly confidential.
        </div>
        <div class="flex items-center space-x-6 text-xs text-gray-600 font-medium pt-2">
            <span class="inline-flex items-center gap-1.5">
                <img src="{{ asset('images/icons/rotate-cw-fading-clock.png') }}" alt="Duration" class="w-4 h-4 object-contain">
                5 - 10 min to complete
            </span>
            <span class="inline-flex items-center gap-1.5">
                <img src="{{ asset('images/icons/layout-list.png') }}" alt="Questions" class="w-4 h-4 object-contain">
                {{ $quiz->questions->count() }} questions
            </span>
        </div>
    </div>

    <div class="flex justify-between items-center pt-4 border-t">
        <a href="{{ route('quiz.index') }}#catalog-section" class="text-gray-600 text-sm hover:underline">&larr; Back to Catalog</a>
        <button type="button" id="startQuizBtn" class="bg-[#2a9d8f] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#23877b] transition">
            Start The Test &rarr;
        </button>
    </div>
</div>

<!-- STATE 2: Step-by-Step Quiz Session View (Matches Figma Session Page) -->
<div id="sessionView" class="hidden max-w-4xl mx-auto">
    <!-- Top Progress Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-bold text-[#12263a]">{{ $quiz->title }}</h3>
            <span id="stepIndicator" class="text-xs font-semibold text-[#2a9d8f] bg-teal-50 px-3 py-1 rounded-full">
                Question 1 of {{ $quiz->questions->count() }}
            </span>
        </div>
        <!-- Full-Width Progress Bar -->
        <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden">
            <div id="progressBar" class="bg-[#2a9d8f] h-full transition-all duration-300" style="width: {{ 100 / max($quiz->questions->count(), 1) }}%"></div>
        </div>
    </div>

    <!-- Question Card Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 max-w-2xl">
        <form id="quizForm" action="{{ route('quiz.store', $quiz->id) }}" method="POST">
            @csrf

            <div id="questionsContainer">
                @foreach ($quiz->questions as $index => $question)
                <div class="question-step {{ $index > 0 ? 'hidden' : '' }}" data-index="{{ $index }}">
                    <p class="font-bold text-[#12263a] text-base mb-5">
                        {{ $index + 1 }}. {{ $question->question_text }}
                    </p>
                    <div class="space-y-3 mb-6">
                        @foreach ($question->options as $option)
                        <label class="option-label flex items-center space-x-3 p-3.5 bg-white border border-gray-200 rounded-md cursor-pointer hover:border-[#2a9d8f] transition">
                            <input
                                type="radio"
                                name="answers[{{ $question->id }}]"
                                value="{{ $option->id }}"
                                data-question="{{ $index }}"
                                class="question-radio text-[#2a9d8f] focus:ring-[#2a9d8f]"
                            >
                            <span class="text-sm text-slate-700">{{ $option->option_text }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                Please answer all questions before submitting.
            </div>
            @endif

            <!-- Card Footer Navigation Matching Figma -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <button type="button" id="prevBtn" class="px-5 py-1.5 bg-white border border-[#2a9d8f] text-[#2a9d8f] rounded-md text-xs font-semibold hover:bg-teal-50 hidden transition">
                        Prev
                    </button>
                    <button type="button" id="quitQuizBtn" class="inline-flex items-center gap-1.5 text-red-600 text-xs font-semibold hover:underline">
                        <img src="{{ asset('images/icons/square-arrow-left-exit.png') }}" alt="Quit" class="w-4 h-4 object-contain">
                        <span>Quit Quiz</span>
                    </button>
                </div>

                <button type="button" id="nextBtn" class="px-6 py-2 bg-[#2a9d8f] text-white rounded-md text-xs font-semibold hover:bg-[#23877b] transition">
                    Next
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quit Confirmation Modal -->
<div id="quitModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl max-w-md w-full shadow-lg">
        <h3 class="text-lg font-bold text-gray-900">Quit Quiz Session?</h3>
        <p class="text-sm text-gray-600 mt-2">Are you sure you want to quit? Your session will be cancelled and data will not be saved.</p>
        <div class="mt-6 flex justify-end space-x-3">
            <button type="button" id="stayQuizBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Continue Quiz</button>
            <a href="{{ route('quiz.index') }}" id="confirmQuitLink" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Yes, Quit</a>
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl max-w-md w-full shadow-lg">
        <h3 class="text-lg font-bold text-gray-900">Confirm Submission</h3>
        <p class="text-sm text-gray-600 mt-2">Are you sure you want to finish the quiz, or would you like to go back to review your answers?</p>
        <div class="mt-6 flex justify-end space-x-3">
            <button type="button" id="goBackBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Go Back</button>
            <button type="button" id="confirmSubmitBtn" class="px-4 py-2 bg-[#2a9d8f] text-white rounded-lg text-sm font-medium hover:bg-[#23877b]">Yes, Finish</button>
        </div>
    </div>
</div>

<script>
    const totalSteps = {{ $quiz->questions->count() }};
    let currentStep = 0;
    let isSubmitting = false;

    document.getElementById('startQuizBtn').addEventListener('click', function() {
        document.getElementById('briefingView').classList.add('hidden');
        document.getElementById('sessionView').classList.remove('hidden');
        updateStepView();
    });

    const steps = document.querySelectorAll('.question-step');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const progressBar = document.getElementById('progressBar');
    const stepIndicator = document.getElementById('stepIndicator');

    // Highlight selected radio box like Figma
    document.querySelectorAll('.question-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const currentStepEl = this.closest('.question-step');
            currentStepEl.querySelectorAll('.option-label').forEach(lbl => {
                lbl.classList.remove('bg-teal-50', 'border-[#2a9d8f]', 'font-semibold');
                lbl.classList.add('bg-white', 'border-gray-200');
            });
            const selectedLabel = this.closest('.option-label');
            selectedLabel.classList.remove('bg-white', 'border-gray-200');
            selectedLabel.classList.add('bg-teal-50', 'border-[#2a9d8f]', 'font-semibold');
        });
    });

    function updateStepView() {
        steps.forEach((step, idx) => {
            step.classList.toggle('hidden', idx !== currentStep);
        });

        prevBtn.classList.toggle('hidden', currentStep === 0);

        if (currentStep === totalSteps - 1) {
            nextBtn.textContent = 'Finish';
        } else {
            nextBtn.textContent = 'Next';
        }

        const progressPercent = ((currentStep + 1) / totalSteps) * 100;
        progressBar.style.width = progressPercent + '%';
        stepIndicator.textContent = `Question ${currentStep + 1} of ${totalSteps}`;
    }

    nextBtn.addEventListener('click', function() {
        const currentQuestionStep = steps[currentStep];
        const radios = currentQuestionStep.querySelectorAll('input[type="radio"]');
        let answered = Array.from(radios).some(r => r.checked);

        if (!answered) {
            alert('Please select an option before proceeding.');
            return;
        }

        if (currentStep < totalSteps - 1) {
            currentStep++;
            updateStepView();
        } else {
            document.getElementById('confirmModal').classList.remove('hidden');
        }
    });

    prevBtn.addEventListener('click', function() {
        if (currentStep > 0) {
            currentStep--;
            updateStepView();
        }
    });

    document.getElementById('quitQuizBtn').addEventListener('click', function() {
        document.getElementById('quitModal').classList.remove('hidden');
    });
    document.getElementById('stayQuizBtn').addEventListener('click', function() {
        document.getElementById('quitModal').classList.add('hidden');
    });
    document.getElementById('confirmQuitLink').addEventListener('click', function() {
        isSubmitting = true;
    });

    document.getElementById('goBackBtn').addEventListener('click', function() {
        document.getElementById('confirmModal').classList.add('hidden');
    });
    document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
        isSubmitting = true;
        document.getElementById('quizForm').submit();
    });

    window.addEventListener('beforeunload', function (e) {
        if (!isSubmitting && document.getElementById('sessionView').classList.contains('hidden') === false) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
@endsection