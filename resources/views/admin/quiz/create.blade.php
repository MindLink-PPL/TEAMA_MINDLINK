@extends('layouts.mindlink')

@section('header_title', 'Wellbeing self check-in: Create New Questionnaire')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.quiz.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#2a9d8f] hover:underline mb-2">
            &larr; Back to Admin Questionnaire List
        </a>
        <h2 class="text-2xl font-extrabold text-[#12263a]">Create New Wellbeing Quiz / Test</h2>
        <p class="text-xs text-slate-600 mt-1">Define the assessment details, questions, and option point values used to calculate student stress levels.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs space-y-1">
            <p class="font-bold">Please fix the following errors:</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.quiz.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Basic Quiz Metadata Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="text-base font-bold text-[#12263a] border-b pb-2">1. Assessment Information</h3>

            <div>
                <label class="block text-xs font-bold text-[#12263a] mb-1">Quiz / Test Title *</label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    placeholder="e.g., Distress Tolerance Scale (DTS)"
                    class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2a9d8f]"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-[#12263a] mb-1">Description & Briefing Instructions *</label>
                <textarea
                    name="description"
                    rows="3"
                    required
                    placeholder="Describe what this wellbeing assessment measures..."
                    class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2a9d8f]"
                >{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    checked
                    class="rounded text-[#2a9d8f] focus:ring-[#2a9d8f]"
                >
                <label for="is_active" class="text-xs font-semibold text-slate-700">
                    Make this quiz active and visible in the student catalog immediately
                </label>
            </div>
        </div>

        <!-- Dynamic Questions & Scoring Options Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-[#12263a]">2. Questions & Scoring Options</h3>
                    <p class="text-xs text-slate-500">Assign point values (e.g., 0 to 3 points) to each answer option.</p>
                </div>
                <button
                    type="button"
                    id="addQuestionBtn"
                    class="bg-[#1d3e53] hover:bg-[#12263a] text-white px-3.5 py-2 rounded-lg text-xs font-bold transition"
                >
                    + Add Question
                </button>
            </div>

            <div id="questionsWrapper" class="space-y-6">
                <!-- Default First Question Block -->
                <div class="question-block border border-slate-200 rounded-lg p-5 bg-slate-50" data-q-index="0">
                    <div class="flex justify-between items-center mb-3">
                        <span class="question-number text-xs font-extrabold text-[#1d3e53] uppercase">Question 1</span>
                        <button type="button" class="remove-question-btn text-xs font-bold text-red-600 hover:underline">Remove Question</button>
                    </div>

                    <div class="mb-4">
                        <input
                            type="text"
                            name="questions[0][question_text]"
                            required
                            placeholder="Enter the question statement..."
                            class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-md text-sm focus:outline-none focus:border-[#2a9d8f]"
                        >
                    </div>

                    <div class="options-wrapper space-y-2 mb-3">
                        @php
                            $defaultOptions = [
                                ['text' => 'Never / Strongly Disagree', 'pts' => 0],
                                ['text' => 'Sometimes / Mildly Agree', 'pts' => 1],
                                ['text' => 'Often / Moderately Agree', 'pts' => 2],
                                ['text' => 'Almost Always / Strongly Agree', 'pts' => 3],
                            ];
                        @endphp
                        @foreach ($defaultOptions as $oIdx => $defOpt)
                            <div class="option-row flex items-center gap-2">
                                <input
                                    type="text"
                                    name="questions[0][options][{{ $oIdx }}][option_text]"
                                    value="{{ $defOpt['text'] }}"
                                    required
                                    placeholder="Option text"
                                    class="flex-1 px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs"
                                >
                                <div class="flex items-center gap-1">
                                    <input
                                        type="number"
                                        name="questions[0][options][{{ $oIdx }}][points]"
                                        value="{{ $defOpt['pts'] }}"
                                        min="0"
                                        required
                                        class="w-20 px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-center font-bold"
                                    >
                                    <span class="text-xs text-slate-500 font-semibold">pts</span>
                                </div>
                                <button type="button" class="remove-option-btn text-red-500 hover:text-red-700 text-xs font-bold px-2">✕</button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="add-option-btn text-xs font-bold text-[#2a9d8f] hover:underline">
                        + Add Option
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-end items-center gap-3">
            <a href="{{ route('admin.quiz.index') }}" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-[#2a9d8f] hover:bg-[#23877b] text-white rounded-lg text-xs font-bold shadow-sm transition">
                Save Questionnaire
            </button>
        </div>
    </form>
</div>

<script>
    let questionCounter = 1;

    function refreshQuestionLabels() {
        document.querySelectorAll('.question-block').forEach((block, idx) => {
            block.querySelector('.question-number').textContent = `Question ${idx + 1}`;
        });
    }

    document.getElementById('addQuestionBtn').addEventListener('click', function () {
        const wrapper = document.getElementById('questionsWrapper');
        const qIdx = questionCounter++;

        const block = document.createElement('div');
        block.className = 'question-block border border-slate-200 rounded-lg p-5 bg-slate-50';
        block.dataset.qIndex = qIdx;
        block.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <span class="question-number text-xs font-extrabold text-[#1d3e53] uppercase">Question</span>
                <button type="button" class="remove-question-btn text-xs font-bold text-red-600 hover:underline">Remove Question</button>
            </div>
            <div class="mb-4">
                <input type="text" name="questions[${qIdx}][question_text]" required placeholder="Enter the question statement..." class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-md text-sm focus:outline-none focus:border-[#2a9d8f]">
            </div>
            <div class="options-wrapper space-y-2 mb-3">
                <div class="option-row flex items-center gap-2">
                    <input type="text" name="questions[${qIdx}][options][0][option_text]" value="Never" required class="flex-1 px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs">
                    <div class="flex items-center gap-1">
                        <input type="number" name="questions[${qIdx}][options][0][points]" value="0" min="0" required class="w-20 px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-center font-bold">
                        <span class="text-xs text-slate-500 font-semibold">pts</span>
                    </div>
                    <button type="button" class="remove-option-btn text-red-500 hover:text-red-700 text-xs font-bold px-2">✕</button>
                </div>
                <div class="option-row flex items-center gap-2">
                    <input type="text" name="questions[${qIdx}][options][1][option_text]" value="Sometimes" required class="flex-1 px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs">
                    <div class="flex items-center gap-1">
                        <input type="number" name="questions[${qIdx}][options][1][points]" value="1" min="0" required class="w-20 px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-center font-bold">
                        <span class="text-xs text-slate-500 font-semibold">pts</span>
                    </div>
                    <button type="button" class="remove-option-btn text-red-500 hover:text-red-700 text-xs font-bold px-2">✕</button>
                </div>
                <div class="option-row flex items-center gap-2">
                    <input type="text" name="questions[${qIdx}][options][2][option_text]" value="Often" required class="flex-1 px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs">
                    <div class="flex items-center gap-1">
                        <input type="number" name="questions[${qIdx}][options][2][points]" value="2" min="0" required class="w-20 px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-center font-bold">
                        <span class="text-xs text-slate-500 font-semibold">pts</span>
                    </div>
                    <button type="button" class="remove-option-btn text-red-500 hover:text-red-700 text-xs font-bold px-2">✕</button>
                </div>
                <div class="option-row flex items-center gap-2">
                    <input type="text" name="questions[${qIdx}][options][3][option_text]" value="Almost Always" required class="flex-1 px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs">
                    <div class="flex items-center gap-1">
                        <input type="number" name="questions[${qIdx}][options][3][points]" value="3" min="0" required class="w-20 px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-center font-bold">
                        <span class="text-xs text-slate-500 font-semibold">pts</span>
                    </div>
                    <button type="button" class="remove-option-btn text-red-500 hover:text-red-700 text-xs font-bold px-2">✕</button>
                </div>
            </div>
            <button type="button" class="add-option-btn text-xs font-bold text-[#2a9d8f] hover:underline">+ Add Option</button>
        `;

        wrapper.appendChild(block);
        refreshQuestionLabels();
    });

    document.getElementById('questionsWrapper').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-question-btn')) {
            const blocks = document.querySelectorAll('.question-block');
            if (blocks.length <= 1) {
                alert('A questionnaire must have at least 1 question.');
                return;
            }
            e.target.closest('.question-block').remove();
            refreshQuestionLabels();
        }

        if (e.target.classList.contains('add-option-btn')) {
            const block = e.target.closest('.question-block');
            const qIdx = block.dataset.qIndex;
            const optionsWrapper = block.querySelector('.options-wrapper');
            const optIdx = Date.now();

            const row = document.createElement('div');
            row.className = 'option-row flex items-center gap-2';
            row.innerHTML = `
                <input type="text" name="questions[${qIdx}][options][${optIdx}][option_text]" required placeholder="Option text" class="flex-1 px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs">
                <div class="flex items-center gap-1">
                    <input type="number" name="questions[${qIdx}][options][${optIdx}][points]" value="0" min="0" required class="w-20 px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-center font-bold">
                    <span class="text-xs text-slate-500 font-semibold">pts</span>
                </div>
                <button type="button" class="remove-option-btn text-red-500 hover:text-red-700 text-xs font-bold px-2">✕</button>
            `;
            optionsWrapper.appendChild(row);
        }

        if (e.target.classList.contains('remove-option-btn')) {
            const optionsWrapper = e.target.closest('.options-wrapper');
            if (optionsWrapper.querySelectorAll('.option-row').length <= 2) {
                alert('Each question must have at least 2 options.');
                return;
            }
            e.target.closest('.option-row').remove();
        }
    });
</script>
@endsection