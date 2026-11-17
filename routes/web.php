<?php

use App\Http\Controllers\ArticleController;

Route::middleware(['auth'])->group(function () {
    Route::get('/learn', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/learn/{article}', [ArticleController::class, 'show'])->name('articles.show');
});
