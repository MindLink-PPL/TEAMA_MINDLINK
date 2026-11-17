<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - MindLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="max-w-3xl mx-auto py-10 px-6 space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">&larr; Back to Mental Health Literacy</a>
            <form action="{{ route('articles.toggleSave', $article) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold border transition {{ $isSaved ? 'bg-amber-50 border-amber-300 text-amber-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-100' }}">
                    {{ $isSaved ? 'Saved ★ (Click to remove)' : 'Save Article ☆' }}
                </button>
            </form>
        </div>
        @if(session('status'))
            <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded-xl text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif
        <article class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-5">
            <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-800 text-xs font-bold uppercase">
                {{ $article->category->category_name ?? 'General' }}
            </span>
            <h1 class="text-2xl font-bold text-slate-900">{{ $article->title }}</h1>
            @if($article->source)
                <p class="text-xs text-slate-400">Source: {{ $article->source }}</p>
            @endif
            <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $article->content }}</div>
            <div class="pt-4 border-t border-slate-100 text-xs text-slate-400">
                Disclaimer: This article is for educational literacy only and does not replace professional psychological assessment.
            </div>
        </article>
    </div>
</body>
</html>
