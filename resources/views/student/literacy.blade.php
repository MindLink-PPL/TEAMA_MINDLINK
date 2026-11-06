<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Mental Health Literacy
        </h1>
    </x-slot>

    <div class="space-y-6" x-data="{ activeArticle: null }">
        <!-- Section Heading -->
        <div>
            <h2 class="text-2xl font-bold text-[#0f2a4a] tracking-tight">Find clear, practical information</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Articles are educational and do not replace professional assessment.
            </p>
        </div>

        <!-- Search Bar (Matching PG-02) -->
        <form method="GET" action="{{ route('student.literacy') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-3xl">
            @if ($selectedTopic !== 'All topics')
                <input type="hidden" name="topic" value="{{ $selectedTopic }}">
            @endif
            <div class="flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search articles, topics, or keywords"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-[#1c9c8c] focus:border-[#1c9c8c] transition shadow-2xs"
                >
            </div>
            <button
                type="submit"
                class="px-6 py-2.5 bg-[#1c9c8c] hover:bg-[#157a6e] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-2xs shrink-0"
            >
                Search
            </button>
        </form>

        <!-- Topic Filter Pills (Matching PG-02) -->
        <div class="flex flex-wrap items-center gap-2.5">
            @foreach ($topics as $topic)
                @php
                    $isActive = strcasecmp($selectedTopic, $topic) === 0;
                @endphp
                <a
                    href="{{ route('student.literacy', array_filter(['topic' => $topic === 'All topics' ? null : $topic, 'search' => $search ?: null])) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ $isActive ? 'bg-[#0f2a4a] text-white shadow-2xs' : 'bg-[#e6f5f0] text-[#1c9c8c] hover:bg-[#d3eee5]' }}"
                >
                    {{ $topic }}
                </a>
            @endforeach
        </div>

        <!-- Articles Grid (Matching PG-02) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse (array_slice($articles, 0, 3) as $article)
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between">
                    <div>
                        <!-- Top Banner Box -->
                        <div class="h-28 rounded-xl {{ $article['banner_bg'] }} flex items-center justify-center">
                            <span class="text-base font-bold text-[#1c9c8c]">{{ $article['initial'] }}</span>
                        </div>

                        <!-- Category Tag -->
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#1c9c8c] mt-4">
                            {{ $article['category_label'] }}
                        </div>

                        <!-- Article Title -->
                        <h3 class="text-base font-bold text-[#0f2a4a] mt-1">
                            {{ $article['title'] }}
                        </h3>

                        <!-- Article Summary -->
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            {{ $article['summary'] }}
                        </p>
                    </div>

                    <!-- Footer: Read Time & Link -->
                    <div class="mt-4 pt-2 flex items-center gap-3 text-xs font-bold text-[#0f2a4a]">
                        <span>{{ $article['read_time'] }}</span>
                        <button
                            type="button"
                            @click="activeArticle = {{ json_encode($article) }}"
                            class="inline-flex items-center gap-1 text-[#0f2a4a] hover:text-[#1c9c8c] transition cursor-pointer"
                        >
                            <span>Read article</span>
                            <span>&gt;</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="md:col-span-3 bg-white rounded-2xl border border-slate-200/90 p-8 text-center">
                    <p class="text-sm font-medium text-slate-600">No literacy articles matched your search criteria.</p>
                    <a href="{{ route('student.literacy') }}" class="inline-block mt-3 text-xs font-bold text-[#1c9c8c] hover:underline">
                        View all topics &rarr;
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Article Reader Modal -->
        <div
            x-show="activeArticle !== null"
            x-transition
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            @click.self="activeArticle = null"
        >
            <div class="bg-white rounded-2xl border border-slate-200 max-w-xl w-full p-6 sm:p-8 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#1c9c8c]" x-text="activeArticle?.category_label"></span>
                    <span class="text-xs font-semibold text-slate-400" x-text="activeArticle?.read_time"></span>
                </div>

                <h3 class="text-xl font-bold text-[#0f2a4a]" x-text="activeArticle?.title"></h3>
                <p class="text-sm text-slate-600 leading-relaxed" x-text="activeArticle?.content"></p>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('student.counselling') }}" class="text-xs font-bold text-[#1c9c8c] hover:underline">
                        Need personal support? Request counselling &rarr;
                    </a>
                    <button
                        type="button"
                        @click="activeArticle = null"
                        class="px-4 py-2 rounded-xl bg-[#0f2a4a] text-white text-xs font-bold hover:bg-[#163a63] transition"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

