<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Articles - MindLink Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="max-w-6xl mx-auto py-10 px-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manage Mental Health Articles</h1>
                <p class="text-xs text-slate-500 mt-1">Create, update, and maintain educational content for students.</p>
            </div>
            <a href="{{ route('admin.articles.create') }}" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm shadow-sm transition">
                + Add Article
            </a>
        </div>

        @if(session('status'))
            <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded-xl text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                        <th class="py-3.5 px-5">Title</th>
                        <th class="py-3.5 px-5">Topic</th>
                        <th class="py-3.5 px-5">Source</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($articles as $article)
                        <tr>
                            <td class="py-3.5 px-5 font-semibold text-slate-900">{{ $article->title }}</td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-full bg-teal-50 text-teal-800 text-xs font-semibold">
                                    {{ $article->category->category_name ?? 'General' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-xs text-slate-500">{{ $article->source ?? '-' }}</td>
                            <td class="py-3.5 px-5 text-right space-x-3 text-xs font-semibold">
                                <a href="{{ route('admin.articles.edit', $article) }}" class="text-teal-700 hover:underline">Edit</a>
                                <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="inline" onsubmit="return confirm('Delete this article?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-xs text-slate-500">No articles found. Click "+ Add Article" to create one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
