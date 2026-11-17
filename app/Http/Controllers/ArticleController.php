<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    private function currentUser()
    {
        return auth()->user() ?? User::firstOrCreate(
            ['email' => 'student@mindlink.test'],
            ['name' => 'Test Student', 'password' => bcrypt('password')]
        );
    }

    public function index(Request $request)
    {
        $categories = ArticleCategory::all();
        $query = Article::with('category')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        $articles = $query->get();
        $savedArticleIds = $this->currentUser()->savedArticles()->pluck('articles.id')->toArray();

        return view('articles.index', compact('articles', 'categories', 'savedArticleIds'));
    }

    public function show(Article $article)
    {
        $article->load('category');
        $isSaved = $this->currentUser()->savedArticles()->where('article_id', $article->id)->exists();

        return view('articles.show', compact('article', 'isSaved'));
    }

    public function toggleSave(Article $article)
    {
        $user = $this->currentUser();

        if ($user->savedArticles()->where('article_id', $article->id)->exists()) {
            $user->savedArticles()->detach($article->id);
            $message = 'Article removed from saved list.';
        } else {
            $user->savedArticles()->attach($article->id, ['saved_at' => now()]);
            $message = 'Article saved for later.';
        }

        return back()->with('status', $message);
    }

    public function saved()
    {
        $articles = $this->currentUser()->savedArticles()->with('category')->orderByPivot('saved_at', 'desc')->get();

        return view('articles.saved', compact('articles'));
    }
}
