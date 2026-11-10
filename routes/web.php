<?php

use App\Http\Controllers\CounsellingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/counselling/request', [CounsellingController::class, 'create'])->name('counselling.request');
Route::post('/counselling/request', [CounsellingController::class, 'store'])->name('counselling.store');

