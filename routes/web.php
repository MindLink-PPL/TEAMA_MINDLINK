<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Admin\AdminArticleController;

    Route::get('/learn', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/learn/saved', [ArticleController::class, 'saved'])->name('articles.saved');
    Route::get('/learn/{article}', [ArticleController::class, 'show'])->name('articles.show');
    Route::post('/learn/{article}/save', [ArticleController::class, 'toggleSave'])->name('articles.toggleSave');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('articles', AdminArticleController::class)->except(['show']);
});
