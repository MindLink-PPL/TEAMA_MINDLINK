<?php
use App\Http\Controllers\CounsellorController;
use App\Http\Controllers\CounsellingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// counselling request student side
Route::get('/counselling/request', [CounsellingController::class, 'create'])->name('counselling.request');
Route::post('/counselling/request', [CounsellingController::class, 'store'])->name('counselling.store');

// counsellor dashboard 
Route::get('/counsellor/dashboard', [CounsellorController::class, 'index'])->name('counsellor.dashboard');
Route::patch('/counsellor/requests/{id}/status', [CounsellorController::class, 'updateStatus'])->name('counsellor.updateStatus');

