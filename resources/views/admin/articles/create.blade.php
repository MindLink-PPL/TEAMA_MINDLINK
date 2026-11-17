<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Article - MindLink Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="max-w-3xl mx-auto py-10 px-6 space-y-6">
        <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">&larr; Back to Article Management</a>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-5">
            <h1 class="text-xl font-bold text-slate-900">Add New Mental Health Article</h1>

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-3 rounded-xl text-xs">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.articles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1.5">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1.5">Topic Category *</label>
                    <select name="category_id" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm bg-white">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1.5">Source / Reference</label>
                    <input type="text" name="source" value="{{ old('source') }}" placeholder="e.g., MindLink Editorial" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1.5">Article Content *</label>
                    <textarea name="content" rows="8" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">{{ old('content') }}</textarea>
                </div>
                <div class="pt-2 flex justify-end space-x-3">
                    <a href="{{ route('admin.articles.index') }}" class="border border-slate-300 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium">Cancel</a>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm shadow-sm">Publish Article</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
