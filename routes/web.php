<?php

use App\Http\Controllers\ArticleController;

Route::group([], function () {
    Route::get('/learn', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/learn/saved', [ArticleController::class, 'saved'])->name('articles.saved');
    Route::get('/learn/{article}', [ArticleController::class, 'show'])->name('articles.show');
    Route::post('/learn/{article}/save', [ArticleController::class, 'toggleSave'])->name('articles.toggleSave');
});
