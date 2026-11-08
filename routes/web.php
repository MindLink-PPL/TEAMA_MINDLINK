<?php

use Illuminate\Support\Facades\Route;

// Route bawaan asli (jangan dihapus)
Route::get('/', function () {
    return view('welcome');
});

// Route baru buatan lu buat ngetes UI Consent
Route::get('/test-consent', function () {
    return view('consent');
});