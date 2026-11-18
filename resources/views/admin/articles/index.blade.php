<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Content Management
        </h1>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#0f2a4a]">Manage Mental Health Articles</h2>
                <p class="text-xs text-slate-500 mt-1">Create, update, and maintain educational content for students.</p>
            </div>
            <a href="{{ route('admin.articles.create') }}" class="bg-[#1c9c8c] hover:bg-[#157a6e] text-white font-semibold px-5 py-2.5 rounded-xl text-sm shadow-2xs transition self-start sm:self-center">
                + Add Article
            </a>
        </div>

        @if(session('status'))
            <div class="bg-[#e6f5f0] border border-[#cdece2] text-[#0f766e] px-4 py-3 rounded-xl text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#f8fafc] border-b border-slate-200 text-xs uppercase text-slate-500">
                        <th class="py-3.5 px-5">Title</th>
                        <th class="py-3.5 px-5">Topic</th>
                        <th class="py-3.5 px-5">Source</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($articles as $article)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5 font-semibold text-[#0f2a4a]">{{ $article->title }}</td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-full bg-[#e6f5f0] text-[#1c9c8c] text-xs font-semibold">
                                    {{ $article->category->category_name ?? 'General' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-xs text-slate-500">{{ $article->source ?? '-' }}</td>
                            <td class="py-3.5 px-5 text-right space-x-3 text-xs font-semibold">
                                <a href="{{ route('admin.articles.edit', $article) }}" class="text-[#1c9c8c] hover:underline">Edit</a>
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
</x-app-layout>
