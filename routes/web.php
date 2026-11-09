<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-consent', function () {
    return view('consent');
});

use App\Http\Controllers\InformedConsentController;

Route::post('/submit-consent', [InformedConsentController::class, 'store']);

Route::get('/admin/consents', [InformedConsentController::class, 'index']);