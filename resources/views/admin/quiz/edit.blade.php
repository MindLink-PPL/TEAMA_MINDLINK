@extends('layouts.mindlink')

@section('header_title', 'Wellbeing self check-in: Edit Questionnaire & Scoring')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.quiz.show', $quiz->id) }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#2a9d8f] hover:underline mb-2">
            &larr; Back to Quiz Details
        </a>
        <h2 class="text-2xl font-extrabold text-[#12263a]">Edit Wellbeing Quiz: {{ $quiz->title }}</h2>
        <p class="text-xs text-slate-600 mt-1">Update the assessment details, questions, or option point scoring below.</p>
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

    <form action="{{ route('admin.quiz.update', $quiz->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Quiz Metadata Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="text-base font-bold text-[#12263a] border-b pb-2">1. Assessment Information</h3>

            <div>
                <label class="block text-xs font-bold text-[#12263a] mb-1">Quiz / Test Title *</label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title', $quiz->title) }}"
                    required
                    class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2a9d8f]"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-[#12263a] mb-1">Description & Briefing Instructions *</label>
                <textarea
                    name="description"
                    rows="3"
                    required
                    class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2a9d8f]"
                >{{ old('description', $quiz->description) }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $quiz->is_active) ? 'checked' : '' }}
                    class="rounded text-[#2a9d8f] focus:ring-[#2a9d8f]"
                >
                <label for="is_active" class="text-xs font-semibold text-slate-700">
                    Active and visible in the student catalog
                </label>
            </div>
        </div>

        <!-- Dynamic Questions & Scoring Options Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-[#12263a]">2. Questions & Scoring Options</h3>
                    <p class="text-xs text-slate-500">Modify questions, option labels, or point values below.</p>
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
                @foreach ($quiz->questions as $qIdx => $question)
                    <div class="question-block border border-slate-200 rounded-lg p-5 bg-slate-50" data-q-index="{{ $qIdx }}">
                        <div class="flex justify-between items-center mb-3">
                            <span class="question-number text-xs font-extrabold text-[#1d3e53] uppercase">Question {{ $qIdx + 1 }}</span>
                            <button type="button" class="remove-question-btn text-xs font-bold text-red-600 hover:underline">Remove Question</button>
                        </div>

                        <div class="mb-4">
                            <input
                                type="text"
                                name="questions[{{ $qIdx }}][question_text]"
                                value="{{ $question->question_text }}"
                                required
                                class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-md text-sm focus:outline-none focus:border-[#2a9d8f]"
                            >
                        </div>

                        <div class="options-wrapper space-y-2 mb-3">
                            @foreach ($question->options as $oIdx => $option)
                                <div class="option-row flex items-center gap-2">
                                    <input
                                        type="text"
                                        name="questions[{{ $qIdx }}][options][{{ $oIdx }}][option_text]"
                                        value="{{ $option->option_text }}"
                                        required
                                        class="flex-1 px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs"
                                    >
                                    <div class="flex items-center gap-1">
                                        <input
                                            type="number"
                                            name="questions[{{ $qIdx }}][options][{{ $oIdx }}][points]"
                                            value="{{ $option->points }}"
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
                @endforeach
            </div>
        </div>

        <div class="flex justify-end items-center gap-3">
            <a href="{{ route('admin.quiz.show', $quiz->id) }}" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-[#2a9d8f] hover:bg-[#23877b] text-white rounded-lg text-xs font-bold shadow-sm transition">
                Update Questionnaire
            </button>
        </div>
    </form>
</div>

<script>
    let questionCounter = {{ $quiz->questions->count() + 1 }};

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