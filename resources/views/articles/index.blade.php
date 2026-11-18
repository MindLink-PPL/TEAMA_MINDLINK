<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
                Mental Health Literacy
            </h1>
            <a href="{{ route('articles.saved') }}" class="bg-white hover:bg-slate-100 text-slate-700 px-3.5 py-1 rounded-full text-xs font-semibold shadow-2xs border border-slate-200 transition">
                Saved ({{ count($savedArticleIds) }})
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('status'))
            <div class="bg-[#e6f5f0] border border-[#cdece2] text-[#0f766e] px-5 py-3.5 rounded-xl text-sm font-medium shadow-2xs">
                {{ session('status') }}
            </div>
        @endif

        <div>
            <h2 class="text-xl font-bold text-[#0f2a4a]">Find clear, practical information</h2>
            <p class="text-sm text-slate-500 mt-1">Articles are educational and do not replace professional assessment.</p>
        </div>

        <form action="{{ route('articles.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if(request('topic'))
                <input type="hidden" name="topic" value="{{ request('topic') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search articles, topics, or keywords"
                   class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#1c9c8c]">
            <button type="submit" class="bg-[#1c9c8c] hover:bg-[#157a6e] text-white font-semibold px-7 py-3 rounded-xl text-sm shadow-2xs transition">
                Search
            </button>
        </form>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('articles.index', ['search' => request('search')]) }}"
               class="px-4 py-1.5 rounded-full text-xs font-semibold transition {{ !request('category') && !request('topic') ? 'bg-[#0f2a4a] text-white' : 'bg-[#e6f5f0] text-[#1c9c8c] hover:bg-[#d0ede3]' }}">
                All topics
            </a>
            @foreach($categories as $category)
                <a href="{{ route('articles.index', ['category' => $category->id, 'search' => request('search')]) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-semibold transition {{ request('category') == $category->id || strcasecmp((string) request('topic'), $category->category_name) === 0 ? 'bg-[#0f2a4a] text-white' : 'bg-[#e6f5f0] text-[#1c9c8c] hover:bg-[#d0ede3]' }}">
                    {{ $category->category_name }}
                </a>
            @endforeach
        </div>

        @if($articles->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/90 p-10 text-center space-y-3">
                <p class="text-base font-bold text-[#0f2a4a]">No articles found</p>
                <p class="text-xs text-slate-500">Try clearing your search keyword or topic filter.</p>
                <a href="{{ route('articles.index') }}" class="inline-block mt-2 text-xs font-semibold text-[#1c9c8c] hover:underline">Clear filters</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $article)
                    @php
                        $isSaved = in_array($article->id, $savedArticleIds);
                        $readMinutes = max(1, ceil(str_word_count(strip_tags($article->content)) / 200));
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="h-28 rounded-xl bg-[#e6f5f0] flex items-center justify-center">
                                <span class="w-11 h-11 rounded-full bg-[#1c9c8c] text-white font-bold flex items-center justify-center text-base">
                                    {{ strtoupper(substr($article->category->category_name ?? 'A', 0, 1)) }}
                                </span>
                            </div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#1c9c8c]">
                                {{ $article->category->category_name ?? 'General' }}
                            </p>
                            <h4 class="text-base font-bold text-[#0f2a4a] leading-snug">{{ $article->title }}</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ Str::limit(strip_tags($article->content), 120) }}</p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-400">{{ $readMinutes }} min read</span>
                            <div class="flex items-center space-x-3">
                                <form action="{{ route('articles.toggleSave', $article) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="font-semibold {{ $isSaved ? 'text-amber-600' : 'text-slate-500 hover:text-[#1c9c8c]' }}">
                                        {{ $isSaved ? 'Saved ★' : 'Save ☆' }}
                                    </button>
                                </form>
                                <a href="{{ route('articles.show', $article) }}" dusk="read-article-{{ $article->id }}" class="font-semibold text-[#1c9c8c] hover:underline">
                                    Read article &gt;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
