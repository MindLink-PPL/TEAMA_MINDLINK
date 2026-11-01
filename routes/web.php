<?php

use App\Http\Controllers\AppointmentController;

Route::middleware('auth')->group(function () {
    Route::get('/counselling/appointments', [AppointmentController::class, 'page'])->name('student.appointments');

    Route::prefix('api')->group(function () {
        Route::get('/counsellors', [AppointmentController::class, 'counsellors']);
        Route::get('/slots', [AppointmentController::class, 'slots']);
        Route::get('/appointments', [AppointmentController::class, 'mine']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
    });
});