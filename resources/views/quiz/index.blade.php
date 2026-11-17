@extends('layouts.mindlink')

@section('header_title', 'Wellbeing self check-in')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- STATE 1: Option Hub Section -->
    <div id="options-section">
        <!-- Top-left Green Exit Badge -->
        <div class="mb-3">
            <a href="/" class="inline-flex items-center gap-2 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm transition">
                <img src="{{ asset('images/icons/square-arrow-left-exit.png') }}" alt="Back" class="w-5 h-5 object-contain brightness-0 invert">
                <span class="leading-tight text-left text-[10px] uppercase tracking-wider">Back to<br>Dashboard</span>
            </a>
        </div>

        <h3 class="text-3xl font-extrabold text-[#12263a] mb-8">Well being self check-in Option:</h3>

        <!-- Two Portrait Cards Matching Figma -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 max-w-3xl mx-auto">
            <!-- Left Card: Question Catalog -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between p-4">
                <div>
                    <!-- Orange Header Block -->
                    <div class="bg-[#f4a261] h-24 rounded-lg flex items-center justify-center mb-5">
                        <img src="{{ asset('images/icons/layout-list.png') }}" alt="Question Catalog" class="w-12 h-12 object-contain brightness-0 invert">
                    </div>
                    <div class="px-2">
                        <h4 class="text-base font-bold text-[#12263a] border-b border-gray-300 pb-2">Question Catalog</h4>
                        <p class="text-xs text-slate-600 mt-3 leading-relaxed">
                            Browse standardized psychological self-assessments designed to help you evaluate and understand your current stress and emotional well-being.
                        </p>
                    </div>
                </div>
                <div class="px-2 mt-8 mb-1">
                    <button type="button" id="openCatalogBtn" class="inline-flex items-center justify-between w-44 bg-[#2a9d8f] hover:bg-[#23877b] text-white py-2.5 px-4 rounded-md text-left font-bold text-xs uppercase tracking-wide transition shadow-sm">
                        <span class="leading-tight">VIEW<br>AVAILABLE<br>QUIZ/TEST</span>
                        <span class="text-base font-bold">&gt;</span>
                    </button>
                </div>
            </div>

            <!-- Right Card: Completed Quiz History -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between p-4">
                <div>
                    <!-- Dark Slate Header Block -->
                    <div class="bg-[#264653] h-24 rounded-lg flex items-center justify-center mb-5">
                        <img src="{{ asset('images/icons/rotate-cw-fading-clock.png') }}" alt="Completed Quiz History" class="w-12 h-12 object-contain brightness-0 invert">
                    </div>
                    <div class="px-2">
                        <h4 class="text-base font-bold text-[#12263a] border-b border-gray-300 pb-2">View your completed quiz/test</h4>
                        <p class="text-xs text-slate-600 mt-3 leading-relaxed">
                            Review your past check-in scores, track your emotional progression over time, and access detailed AI feedback from previous sessions.
                        </p>
                    </div>
                </div>
                <div class="px-2 mt-8 mb-1">
                    <a href="{{ route('quiz.history') }} " class="inline-flex items-center justify-between w-44 bg-[#2a9d8f] hover:bg-[#23877b] text-white py-2.5 px-4 rounded-md text-left font-bold text-xs uppercase tracking-wide transition shadow-sm">
                        <span class="leading-tight">VIEW<br>COMPLETED<br>QUIZ/TEST</span>
                        <span class="text-base font-bold">&gt;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- STATE 2: Quiz Catalog Grid Section -->
    <div id="catalog-section" class="hidden">
        <!-- Top-left Green Exit Badge -->
        <div class="mb-3">
            <button type="button" id="backToOptionsBtn" class="inline-flex items-center gap-2 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm transition">
                <img src="{{ asset('images/icons/square-arrow-left-exit.png') }}" alt="Back" class="w-5 h-5 object-contain brightness-0 invert">
                <span class="leading-tight text-left text-[10px] uppercase tracking-wider">Back to<br>Options</span>
            </button>
        </div>

        <!-- Catalog Header -->
        <div class="mb-4">
            <h3 class="text-lg font-bold text-[#12263a]">self-service check-in that helps manage your well-being</h3>
            <p class="text-xs text-slate-500 mt-1">
                Select an assessment below to evaluate how stress or anxiety may affect thoughts, sleep, and concentration, and when to seek support.
            </p>
        </div>

        <!-- Search Bar Row -->
        <div class="flex items-center gap-4 w-full max-w-4xl mb-8">
            <input
                type="text"
                id="quizSearch"
                placeholder="Search assessments..."
                class="flex-1 bg-white border border-gray-200 rounded-full px-5 py-2.5 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#2a9d8f]"
            >
            <button
                type="button"
                id="quizSearchBtn"
                class="bg-[#2a9d8f] hover:bg-[#23877b] text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition inline-flex items-center justify-center gap-2"
            >
                <span>&rarr;</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>

        <!-- Quiz Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="quizGrid">
            @foreach ($quizzes as $quiz)
            <div class="quiz-card bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col justify-between" data-title="{{ strtolower($quiz->title) }}">
                <div>
                    <span class="text-xs font-semibold text-[#2a9d8f] uppercase tracking-wider">STRESS HANDLING</span>
                    <h4 class="text-lg font-bold text-[#12263a] mt-1 leading-snug">{{ $quiz->title }}</h4>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">{{ Str::limit($quiz->description, 95) }}</p>
                    <p class="text-xs font-bold text-[#12263a] mt-3">
                        5 - 10 min to complete, &nbsp; up to {{ $quiz->questions_count ?? 30 }} questions
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ route('quiz.show', $quiz->id) }}" class="inline-flex items-center justify-between w-40 bg-[#2a9d8f] hover:bg-[#23877b] text-white py-2.5 px-4 rounded-md text-xs font-bold uppercase tracking-wide transition shadow-sm">
                        <span class="leading-tight text-left">VIEW<br>DETAILS</span>
                        <span class="text-base font-bold">&gt;</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    const optionsSection = document.getElementById('options-section');
    const catalogSection = document.getElementById('catalog-section');
    const quizSearchInput = document.getElementById('quizSearch');
    const quizSearchBtn = document.getElementById('quizSearchBtn');

    function showCatalog() {
        optionsSection.classList.add('hidden');
        catalogSection.classList.remove('hidden');
        window.location.hash = 'catalog-section';
    }

    function showOptions() {
        catalogSection.classList.add('hidden');
        optionsSection.classList.remove('hidden');
        history.pushState("", document.title, window.location.pathname + window.location.search);
    }

    function filterQuizzes() {
        const query = quizSearchInput.value.toLowerCase().trim();
        document.querySelectorAll('.quiz-card').forEach(card => {
            const title = card.getAttribute('data-title');
            card.style.display = title.includes(query) ? 'flex' : 'none';
        });
    }

    document.getElementById('openCatalogBtn').addEventListener('click', showCatalog);
    document.getElementById('backToOptionsBtn').addEventListener('click', showOptions);

    if (window.location.hash === '#catalog-section') {
        showCatalog();
    }

    quizSearchInput.addEventListener('input', filterQuizzes);
    quizSearchBtn.addEventListener('click', filterQuizzes);
</script>
@endsection