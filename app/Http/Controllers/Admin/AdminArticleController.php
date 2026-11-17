<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class AdminArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with('category')->latest()->get();
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = ArticleCategory::all();
        return view('admin.articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:article_categories,id',
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'source'      => 'nullable|string|max:255',
        ]);

        Article::create($validated);

        return redirect()->route('admin.articles.index')->with('status', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $categories = ArticleCategory::all();
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:article_categories,id',
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'source'      => 'nullable|string|max:255',
        ]);

        $article->update($validated);

        return redirect()->route('admin.articles.index')->with('status', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route('admin.articles.index')->with('status', 'Article deleted.');
    }
}
