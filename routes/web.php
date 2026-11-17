<?php

use App\Http\Controllers\QuestionnaireController;
use Illuminate\Support\Facades\Route;

// 1. Keep your teammate's static landing page at '/' (or temporarily swap to redirect to quiz.index while testing)
Route::get('/', function () {
    return redirect()->route('quiz.index');
});

// 2. Define all specific module routes BEFORE any wildcard catch-all
Route::prefix('wellbeing')->name('quiz.')->group(function () {
    Route::get('/', [QuestionnaireController::class, 'index'])->name('index');
    Route::get('/results/{id}', [QuestionnaireController::class, 'result'])->name('result');
    Route::get('/{id}', [QuestionnaireController::class, 'show'])->name('show');
    Route::post('/{id}/submit', [QuestionnaireController::class, 'store'])->name('store');
});

// 3. Catch-all fallback MUST always be the very last route in the file
Route::get('/{any}', function () {
    return response()->file(base_path('index.html'));
})->where('any', '.*');