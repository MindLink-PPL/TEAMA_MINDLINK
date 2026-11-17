@extends('layouts.mindlink')

@section('header_title', 'Wellbeing self check-in: Quiz result')

@section('content')
@php
    $lowMax = floor($maxScore * 0.33);
    $modMin = $lowMax + 1;
    $modMax = floor($maxScore * 0.66);
    $highMin = $modMax + 1;
@endphp

<div class="max-w-5xl mx-auto">
    <!-- Top Split Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 text-sm text-[#12263a]">
        <div>
            <p class="font-semibold">
                Your Score: <span class="font-bold">{{ $result->total_score }} points</span> in {{ $result->quiz->title }}
            </p>
            <p class="text-xs text-slate-600 font-medium">(Distress Score / Max {{ $maxScore }} points)</p>
        </div>
        <div class="mt-2 md:mt-0 md:text-right">
            <p class="text-xs font-semibold text-slate-600">Date of quiz/test taken:</p>
            <p class="text-sm font-bold text-[#12263a]">{{ $result->created_at->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Main Stress Level Headline -->
    <h2 class="text-2xl md:text-3xl font-extrabold text-[#12263a] mb-6 leading-snug">
        "You are likely to have a {{ strtolower($result->distress_level) }} level of stress"
    </h2>

    <!-- Side-by-Side Dark Navy Cards (AI Summary + Thresholds) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
        <!-- Left Box: AI Summary (2 columns wide) -->
        <div class="md:col-span-2 bg-[#1d3e53] text-white rounded-lg p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold border-b border-slate-500 pb-2 mb-3">AI Summary:</h3>
                <div class="max-h-44 overflow-y-auto pr-2 text-xs leading-relaxed text-slate-100 space-y-2">
                    <p>
                        {!! nl2br(e($result->ai_summary ?? 'Summary is currently unavailable.')) !!}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Box: Score Threshold Guide (1 column wide) -->
        <div class="bg-[#1d3e53] text-white rounded-lg p-5 shadow-sm">
            <h3 class="text-sm font-bold border-b border-slate-500 pb-2 mb-3">
                {{ $result->quiz->title }} Threshold:
            </h3>
            <ul class="text-xs space-y-3 text-slate-100 leading-relaxed">
                <li>
                    <span class="font-bold">• 0 - {{ $lowMax }} points:</span><br>
                    Low level of stress
                </li>
                <li>
                    <span class="font-bold">• {{ $modMin }} - {{ $modMax }} points:</span><br>
                    Moderate level of stress
                </li>
                <li>
                    <span class="font-bold">• {{ $highMin }} - {{ $maxScore }} points:</span><br>
                    High level of stress
                </li>
            </ul>
        </div>
    </div>

    <!-- Vivid Red AI Disclaimer Banner -->
    <div class="bg-[#c81e1e] text-white rounded-lg p-4 mb-6 shadow-sm">
        <p class="text-xs leading-relaxed">
            <strong class="text-sm font-bold">AI disclaimer:</strong>
            This assessment result and summary are strictly for self-reflection and educational guidance, not a clinical diagnosis. MindLink is not an emergency service—if you feel overwhelmed or in immediate danger, please contact campus counselling or emergency support immediately.
        </p>
    </div>

    <!-- Bottom Action Buttons With Custom Icons -->
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
        <!-- Left Button: Back to Wellbeing Page -->
        <a href="{{ route('quiz.index') }}" class="inline-flex items-center gap-3 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-4 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
            <img src="{{ asset('images/icons/square-arrow-left-exit.png') }}" alt="Back" class="w-6 h-6 object-contain brightness-0 invert">
            <span class="leading-tight text-left">Back to wellbeing<br>self check-in page</span>
        </a>

        <!-- Right Buttons: Book Consultation & View History -->
        <div class="flex flex-wrap items-center gap-4">
            <a href="{{ route('counselling.request') }}" class="inline-flex items-center gap-3 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-4 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
                <span class="leading-tight text-left">Book a<br>consultation<br>session now!</span>
                <img src="{{ asset('images/icons/calendar-plus.png') }}" alt="Book Consultation" class="w-6 h-6 object-contain brightness-0 invert">
            </a>

            <a href="{{ route('quiz.history') }}" class="inline-flex items-center gap-3 bg-[#f3d37a] hover:bg-[#e9c46a] text-[#12263a] px-4 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
                <span class="leading-tight text-left">View your<br>previous quiz/test<br>result list</span>
                <img src="{{ asset('images/icons/rotate-cw-fading-clock.png') }}" alt="History List" class="w-6 h-6 object-contain">
            </a>
        </div>
    </div>
</div>
@endsection