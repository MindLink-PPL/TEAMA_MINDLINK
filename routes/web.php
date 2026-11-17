<?php

use App\Http\Controllers\AdminQuestionnaireController;
use App\Http\Controllers\QuestionnaireController;
use Illuminate\Support\Facades\Route;

// static alnding forced to quiz.index
Route::get('/', function () {
    return redirect()->route('quiz.index');
});

// student quiz fetaure CRUD Routes
Route::prefix('wellbeing')->name('quiz.')->group(function () {
    Route::get('/', [QuestionnaireController::class, 'index'])->name('index');
    Route::get('/results/{id}', [QuestionnaireController::class, 'result'])->name('result');
    Route::get('/history', [QuestionnaireController::class, 'history'])->name('history');
    Route::get('/{id}', [QuestionnaireController::class, 'show'])->name('show');
    Route::post('/{id}/submit', [QuestionnaireController::class, 'store'])->name('store');
});

// Admin Wellbeing Questionnaire CRUD Routes
Route::prefix('admin/wellbeing')->name('admin.quiz.')->group(function () {
    Route::get('/', [AdminQuestionnaireController::class, 'index'])->name('index');
    Route::get('/create', [AdminQuestionnaireController::class, 'create'])->name('create');
    Route::post('/', [AdminQuestionnaireController::class, 'store'])->name('store');
    Route::get('/{id}', [AdminQuestionnaireController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [AdminQuestionnaireController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdminQuestionnaireController::class, 'update'])->name('update');
    Route::delete('/{id}', [AdminQuestionnaireController::class, 'destroy'])->name('destroy');
});

// fallbakc route, stuff like that
Route::get('/{any}', function () {
    return response()->file(base_path('index.html'));
})->where('any', '.*');