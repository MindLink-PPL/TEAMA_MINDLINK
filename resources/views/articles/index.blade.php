<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mental Health Literacy - MindLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between hidden md:flex">
            <div>
                <div class="p-6 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-600 flex items-center justify-center font-bold text-lg text-white">M</div>
                    <div>
                        <h1 class="font-bold text-lg tracking-tight">MindLink</h1>
                        <p class="text-xs text-slate-400">Support platform</p>
                    </div>
                </div>
                <nav class="mt-4 px-4 space-y-1">
                    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-teal-400 font-bold">D</span>
                        Dashboard
                    </a>
                    <a href="{{ route('articles.index') }}" class="flex items-center px-4 py-3 bg-slate-800 text-white rounded-xl font-medium shadow-sm">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-teal-600 text-xs text-white font-bold">L</span>
                        Learn
                    </a>
                    <a href="{{ route('articles.saved') }}" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-teal-400 font-bold">S</span>
                        Saved Articles
                    </a>
                    <a href="{{ route('quiz.index') }}" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-teal-400 font-bold">W</span>
                        Wellbeing
                    </a>
                    <a href="{{ route('counselling.request') }}" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-teal-400 font-bold">C</span>
                        Counselling
                    </a>
                </nav>
            </div>
            <div class="p-4 m-4 bg-slate-800/60 rounded-2xl border border-slate-700/50 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">{{ auth()->user()->name ?? 'Student' }}</p>
                    <p class="text-xs text-slate-400">Secure account</p>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <header class="bg-slate-50 border-b border-slate-200 px-8 py-5 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-slate-900">Mental Health Literacy</h2>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('articles.saved') }}" class="bg-white hover:bg-slate-100 text-slate-700 px-4 py-1.5 rounded-full text-xs font-semibold shadow-sm border border-slate-200 transition">
                        Saved ({{ count($savedArticleIds) }})
                    </a>
                    <div class="bg-slate-100 text-slate-600 px-4 py-1.5 rounded-full text-xs font-semibold shadow-sm border border-slate-200">
                        Student portal
                    </div>
                </div>
            </header>

            <div class="p-8 max-w-7xl mx-auto w-full space-y-6">
                @if(session('status'))
                    <div class="bg-teal-50 border border-teal-200 text-teal-800 px-5 py-3.5 rounded-xl text-sm font-medium shadow-sm">
                        {{ session('status') }}
                    </div>
                @endif
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Find clear, practical information</h3>
                    <p class="text-sm text-slate-500 mt-1">Articles are educational and do not replace professional assessment.</p>
                </div>

                <form action="{{ route('articles.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search articles, topics, or keywords"
                           class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-600">
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-7 py-3 rounded-xl text-sm shadow-sm transition">
                        Search
                    </button>
                </form>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('articles.index', ['search' => request('search')]) }}"
                       class="px-4 py-1.5 rounded-full text-xs font-semibold transition {{ !request('category') ? 'bg-slate-900 text-white' : 'bg-teal-50 text-teal-800 hover:bg-teal-100' }}">
                        All topics
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('articles.index', ['category' => $category->id, 'search' => request('search')]) }}"
                           class="px-4 py-1.5 rounded-full text-xs font-semibold transition {{ request('category') == $category->id ? 'bg-slate-900 text-white' : 'bg-teal-50 text-teal-800 hover:bg-teal-100' }}">
                            {{ $category->category_name }}
                        </a>
                    @endforeach
                </div>

                @if($articles->isEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center space-y-3">
                        <p class="text-base font-bold text-slate-800">No articles found</p>
                        <p class="text-xs text-slate-500">Try clearing your search keyword or topic filter.</p>
                        <a href="{{ route('articles.index') }}" class="inline-block mt-2 text-xs font-semibold text-teal-700 hover:underline">Clear filters</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($articles as $article)
                            @php
                                $isSaved = in_array($article->id, $savedArticleIds);
                                $readMinutes = max(1, ceil(str_word_count(strip_tags($article->content)) / 200));
                            @endphp
                            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    <div class="h-28 rounded-xl bg-teal-50 flex items-center justify-center">
                                        <span class="w-11 h-11 rounded-full bg-teal-600 text-white font-bold flex items-center justify-center text-base">
                                            {{ strtoupper(substr($article->category->category_name ?? 'A', 0, 1)) }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-teal-700">
                                        {{ $article->category->category_name ?? 'General' }}
                                    </p>
                                    <h4 class="text-base font-bold text-slate-900 leading-snug">{{ $article->title }}</h4>
                                    <p class="text-xs text-slate-500 leading-relaxed">{{ Str::limit(strip_tags($article->content), 120) }}</p>
                                </div>
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-400">{{ $readMinutes }} min read</span>
                                    <div class="flex items-center space-x-3">
                                        <form action="{{ route('articles.toggleSave', $article) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="font-semibold {{ $isSaved ? 'text-amber-600' : 'text-slate-500 hover:text-teal-700' }}">
                                                {{ $isSaved ? 'Saved ★' : 'Save ☆' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('articles.show', $article) }}" class="font-semibold text-teal-700 hover:underline">
                                            Read article &gt;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>