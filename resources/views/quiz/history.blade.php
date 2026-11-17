@extends('layouts.mindlink')

@section('header_title', 'Wellbeing self check-in: history list')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Top-left Green Exit Badge -->
    <div class="mb-3">
        <a href="{{ route('quiz.index') }}" class="inline-flex items-center gap-2 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm transition">
            <img src="{{ asset('images/icons/square-arrow-left-exit.png') }}" alt="Back" class="w-5 h-5 object-contain brightness-0 invert">
            <span class="leading-tight text-left text-[10px] uppercase tracking-wider">Back to<br>Options</span>
        </a>
    </div>

    <h3 class="text-2xl font-extrabold text-[#12263a] mb-4">history list of taken test/quiz</h3>

    <!-- Search Bar Row Matching Figma -->
    <div class="flex items-center gap-4 w-full max-w-4xl mb-6">
        <input
            type="text"
            id="historySearch"
            placeholder="Search by quiz title or stress level..."
            class="flex-1 bg-white border border-gray-200 rounded-full px-5 py-2.5 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#2a9d8f]"
        >
        <button
            type="button"
            id="historySearchBtn"
            class="bg-[#2a9d8f] hover:bg-[#23877b] text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition inline-flex items-center justify-center gap-2"
        >
            <span>&rarr;</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
    </div>

    <!-- Dark Navy & Slate Table Matching Figma -->
    <div class="rounded-lg overflow-hidden shadow-sm border border-slate-700">
        <table class="w-full text-left border-collapse">
            <thead class="bg-[#0b1622] text-white text-xs font-bold lowercase tracking-wide">
                <tr>
                    <th class="py-3.5 px-6">session id</th>
                    <th class="py-3.5 px-6">quiz name</th>
                    <th class="py-3.5 px-6">date taken</th>
                    <th class="py-3.5 px-6">result</th>
                    <th class="py-3.5 px-6 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-600 text-xs text-white" id="historyTableBody">
                @forelse ($pastResults as $item)
                <tr class="history-row bg-[#23445b] hover:bg-[#2b526e] transition" data-search="{{ strtolower($item->quiz->title . ' ' . $item->distress_level) }}">
                    <td class="py-4 px-6 font-bold">{{ $item->id }}</td>
                    <td class="py-4 px-6 font-semibold">{{ $item->quiz->title }}</td>
                    <td class="py-4 px-6">{{ $item->created_at->format('d M Y, H:i') }}</td>
                    <td class="py-4 px-6 font-semibold">
                        {{ strtolower($item->distress_level) }} level of stress
                        <span class="block text-[11px] text-slate-300 font-normal">({{ $item->total_score }} pts)</span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <a href="{{ route('quiz.result', $item->id) }}" class="inline-flex items-center gap-2 bg-[#2a9d8f] hover:bg-[#23877b] text-white px-3 py-1.5 rounded-md shadow-sm transition" title="View Result Details">
                            <img src="{{ asset('images/icons/layout-list.png') }}" alt="Details" class="w-4 h-4 object-contain brightness-0 invert">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr class="bg-[#23445b]">
                    <td colspan="5" class="py-8 px-6 text-center text-slate-200 text-sm">
                        You haven't completed any wellbeing check-ins yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const historySearchInput = document.getElementById('historySearch');
    const historySearchBtn = document.getElementById('historySearchBtn');

    function filterHistory() {
        const query = historySearchInput.value.toLowerCase().trim();
        document.querySelectorAll('.history-row').forEach(row => {
            const text = row.getAttribute('data-search');
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    historySearchInput.addEventListener('input', filterHistory);
    historySearchBtn.addEventListener('click', filterHistory);
</script>
@endsection