<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Articles - MindLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="max-w-5xl mx-auto py-10 px-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Saved Articles</h1>
                <p class="text-xs text-slate-500 mt-1">Articles you bookmarked for later reading.</p>
            </div>
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">&larr; Browse All Articles</a>
        </div>

        @if(session('status'))
            <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded-xl text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if($articles->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center space-y-2">
                <p class="text-sm font-bold text-slate-800">No saved articles yet</p>
                <p class="text-xs text-slate-500">Save articles while browsing Mental Health Literacy to find them here later.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($articles as $article)
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold uppercase text-teal-700">{{ $article->category->category_name ?? 'General' }}</span>
                            <h3 class="text-base font-bold text-slate-900">{{ $article->title }}</h3>
                            <p class="text-xs text-slate-500">{{ Str::limit(strip_tags($article->content), 120) }}</p>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
                            <form action="{{ route('articles.toggleSave', $article) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-rose-600 font-semibold hover:underline">Remove</button>
                            </form>
                            <a href="{{ route('articles.show', $article) }}" class="text-teal-700 font-semibold hover:underline">Read article &gt;</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
