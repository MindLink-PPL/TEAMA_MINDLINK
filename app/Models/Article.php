<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['category_id', 'title', 'content', 'source'];

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }
    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_articles', 'article_id', 'user_id')
                    ->withPivot('saved_at');
    }
}
