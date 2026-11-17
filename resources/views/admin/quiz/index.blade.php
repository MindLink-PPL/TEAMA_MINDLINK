@extends('layouts.mindlink')

@section('header_title', 'Wellbeing self check-in: Admin Questionnaire Management')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Top Header & Create Button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <a href="{{ route('quiz.index') }}" class="inline-flex items-center gap-2 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm transition mb-2">
                <img src="{{ asset('images/icons/square-arrow-left-exit.png') }}" alt="Back" class="w-4 h-4 object-contain brightness-0 invert">
                <span>Student View</span>
            </a>
            <h2 class="text-2xl font-extrabold text-[#12263a]">
                Manage wellbeing test/quiz catalog
            </h2>
        </div>

        <a href="{{ route('admin.quiz.create') }}" class="inline-flex items-center gap-2 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-4 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
            <span>+ Create New Quiz/Test</span>
        </a>
    </div>

    @if (session('success'))
        <div class="mb-5 p-4 bg-teal-50 border border-teal-200 text-teal-900 rounded-lg text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search Bar (Matching History List UI) -->
    <form action="{{ route('admin.quiz.index') }}" method="GET" class="flex items-center gap-3 mb-6 max-w-2xl">
        <div class="relative flex-1">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search quiz or test title..."
                class="w-full pl-4 pr-4 py-2 bg-white border border-slate-300 rounded-md text-xs text-slate-700 focus:outline-none focus:border-[#2a9d8f]"
            >
        </div>
        <button type="submit" class="bg-[#2a9d8f] hover:bg-[#23877b] text-white px-5 py-2 rounded-md text-xs font-bold transition">
            Search
        </button>
        @if ($search !== '')
            <a href="{{ route('admin.quiz.index') }}" class="text-xs text-slate-500 hover:underline">Reset</a>
        @endif
    </form>

    <!-- Dark Navy List Table (Matching History Table UI) -->
    <div class="rounded-lg overflow-hidden shadow-md border border-[#12263a]">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#12263a] text-white text-xs font-bold">
                    <th class="py-3.5 px-4 w-20 text-center">quiz id</th>
                    <th class="py-3.5 px-4">quiz name</th>
                    <th class="py-3.5 px-4 text-center">questions</th>
                    <th class="py-3.5 px-4 text-center">max score</th>
                    <th class="py-3.5 px-4 text-center">date created</th>
                    <th class="py-3.5 px-4 text-center">status</th>
                    <th class="py-3.5 px-4 text-right">actions</th>
                </tr>
            </thead>
            <tbody class="text-white text-xs">
                @forelse ($quizzes as $index => $quiz)
                    @php
                        $maxScore = 0;
                        foreach ($quiz->questions as $q) {
                            $maxScore += $q->options->max('points') ?? 0;
                        }
                        $rowBg = $index % 2 === 0 ? 'bg-[#1d3e53]' : 'bg-[#2b526a]';
                    @endphp
                    <tr class="{{ $rowBg }} border-t border-slate-600/50 hover:bg-[#163041] transition">
                        <td class="py-4 px-4 text-center font-bold">{{ $quiz->id }}</td>
                        <td class="py-4 px-4">
                            <p class="font-bold text-sm text-white">{{ $quiz->title }}</p>
                            <p class="text-[11px] text-slate-300 line-clamp-1 mt-0.5">{{ $quiz->description }}</p>
                        </td>
                        <td class="py-4 px-4 text-center font-semibold">{{ $quiz->questions_count }} items</td>
                        <td class="py-4 px-4 text-center font-semibold">{{ $maxScore }} pts</td>
                        <td class="py-4 px-4 text-center">{{ $quiz->created_at ? $quiz->created_at->format('d F Y') : '-' }}</td>
                        <td class="py-4 px-4 text-center">
                            @if ($quiz->is_active)
                                <span class="px-2.5 py-1 bg-teal-500/20 border border-teal-400 text-teal-200 rounded-full text-[11px] font-bold">
                                    Active
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-500/30 border border-slate-400 text-slate-300 rounded-full text-[11px] font-semibold">
                                    Hidden
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.quiz.show', $quiz->id) }}" class="inline-flex items-center gap-1.5 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-3 py-1.5 rounded-md text-[11px] font-bold transition" title="View Questions & Scoring">
                                    <span>Details</span>
                                    <img src="{{ asset('images/icons/layout-list.png') }}" alt="Details" class="w-3.5 h-3.5 object-contain brightness-0 invert">
                                </a>
                                <a href="{{ route('admin.quiz.edit', $quiz->id) }}" class="bg-[#f3d37a] hover:bg-[#e9c46a] text-[#12263a] px-3 py-1.5 rounded-md text-[11px] font-bold transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.quiz.destroy', $quiz->id) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($quiz->title) }} and all of its questions?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-[#c81e1e] hover:bg-red-800 text-white px-3 py-1.5 rounded-md text-[11px] font-bold transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-[#1d3e53]">
                        <td colspan="7" class="py-8 px-4 text-center text-slate-300 text-sm">
                            No wellbeing questionnaires found. Click "+ Create New Quiz/Test" to add one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection