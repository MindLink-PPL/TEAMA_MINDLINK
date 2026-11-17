@extends('layouts.mindlink')

@section('header_title', 'Wellbeing self check-in: Admin Quiz Details & Scoring')

@section('content')
@php
    $lowMax = floor($maxScore * 0.33);
    $modMin = $lowMax + 1;
    $modMax = floor($maxScore * 0.66);
    $highMin = $modMax + 1;
@endphp

<div class="max-w-5xl mx-auto">
    <!-- Top Navigation & Actions -->
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <a href="{{ route('admin.quiz.index') }}" class="inline-flex items-center gap-2 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition">
            <img src="{{ asset('images/icons/square-arrow-left-exit.png') }}" alt="Back" class="w-4 h-4 object-contain brightness-0 invert">
            <span>Back to Admin Quiz List</span>
        </a>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('quiz.show', $quiz->id) }}" class="bg-white border border-[#2a9d8f] text-[#2a9d8f] hover:bg-teal-50 px-4 py-2 rounded-lg text-xs font-bold transition">
                Preview Student Quiz
            </a>
            <a href="{{ route('admin.quiz.edit', $quiz->id) }}" class="bg-[#f3d37a] hover:bg-[#e9c46a] text-[#12263a] px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition">
                Edit Quiz & Scoring
            </a>
            <form action="{{ route('admin.quiz.destroy', $quiz->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this quiz?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-[#c81e1e] hover:bg-red-800 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition">
                    Delete Quiz
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-5 p-4 bg-teal-50 border border-teal-200 text-teal-900 rounded-lg text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Overview & Scoring System Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <!-- Left Box: Quiz Metadata & Description -->
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-xs font-bold text-teal-600 uppercase tracking-wide">Diagnostic Assessment</span>
                    @if ($quiz->is_active)
                        <span class="px-2.5 py-0.5 bg-teal-50 border border-teal-300 text-teal-800 rounded-full text-xs font-bold">Active in Student Catalog</span>
                    @else
                        <span class="px-2.5 py-0.5 bg-gray-100 border border-gray-300 text-gray-600 rounded-full text-xs font-bold">Hidden from Students</span>
                    @endif
                </div>

                <h2 class="text-2xl font-extrabold text-[#12263a]">{{ $quiz->title }}</h2>
                <p class="text-xs text-slate-500 mt-1 mb-4">
                    Quiz ID: #{{ $quiz->id }} • Created {{ $quiz->created_at ? $quiz->created_at->format('d F Y, H:i') : '-' }}
                </p>

                <p class="text-sm text-slate-700 leading-relaxed border-t border-gray-100 pt-4">
                    {{ $quiz->description }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-6 text-xs text-slate-600 font-semibold pt-4 mt-4 border-t border-gray-100">
                <span>Total Questions: <strong class="text-[#12263a]">{{ $quiz->questions->count() }}</strong></span>
                <span>Maximum Possible Score: <strong class="text-[#12263a]">{{ $maxScore }} points</strong></span>
            </div>
        </div>

        <!-- Right Box: Scoring System & Threshold Guide -->
        <div class="bg-[#1d3e53] text-white rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-bold border-b border-slate-500 pb-2 mb-3">
                Scoring System & Thresholds:
            </h3>
            <p class="text-[11px] text-slate-300 mb-3">
                Total score is summed from selected option points (Max {{ $maxScore }} pts) and classified into:
            </p>
            <ul class="text-xs space-y-3 text-slate-100 leading-relaxed">
                <li class="bg-[#12263a]/60 p-2.5 rounded-md border border-teal-500/30">
                    <span class="font-bold text-teal-300">• 0 - {{ $lowMax }} points (≤ 33%):</span><br>
                    Low level of stress
                </li>
                <li class="bg-[#12263a]/60 p-2.5 rounded-md border border-amber-400/30">
                    <span class="font-bold text-amber-300">• {{ $modMin }} - {{ $modMax }} points (34% - 66%):</span><br>
                    Moderate level of stress
                </li>
                <li class="bg-[#12263a]/60 p-2.5 rounded-md border border-red-400/30">
                    <span class="font-bold text-red-300">• {{ $highMin }} - {{ $maxScore }} points (> 66%):</span><br>
                    High level of stress
                </li>
            </ul>
        </div>
    </div>

    <!-- Question List & Option Scoring Breakdown -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-5">
            <div>
                <h3 class="text-lg font-extrabold text-[#12263a]">Questionnaire Items & Option Scoring</h3>
                <p class="text-xs text-slate-500">Each option below displays the exact distress points added when selected by a student.</p>
            </div>
            <span class="text-xs font-bold text-[#2a9d8f] bg-teal-50 px-3 py-1 rounded-full">
                {{ $quiz->questions->count() }} Questions
            </span>
        </div>

        <div class="space-y-5">
            @forelse ($quiz->questions as $qIndex => $question)
                <div class="border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                    <div class="flex justify-between items-start gap-4 mb-3">
                        <p class="font-bold text-sm text-[#12263a]">
                            {{ $qIndex + 1 }}. {{ $question->question_text }}
                        </p>
                        <span class="text-[11px] font-semibold text-slate-500 whitespace-nowrap">
                            Max: {{ $question->options->max('points') ?? 0 }} pts
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach ($question->options as $option)
                            <div class="flex items-center justify-between bg-white border border-slate-200 rounded-md px-3.5 py-2 text-xs">
                                <span class="text-slate-700 font-medium">{{ $option->option_text }}</span>
                                <span class="ml-3 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#1d3e53] text-white whitespace-nowrap">
                                    {{ $option->points }} {{ $option->points === 1 ? 'pt' : 'pts' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-6">No questions have been added to this quiz yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection