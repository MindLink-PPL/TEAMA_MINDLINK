<?php

use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\StudentPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user()->load('role.permissions');
    $upcomingAppointment = null;
    $latestMood = null;

    if ($user->isStudent()) {
        $upcomingAppointment = $user->counsellingRequests()->latest()->first();
        $latestMood = $user->wellbeingCheckins()->whereNotNull('mood')->latest()->value('mood');
    }

    return view('dashboard', compact('user', 'upcomingAppointment', 'latestMood'));
})->middleware(['auth', 'verified'])->name('dashboard');

// Administrator Routes (Protected by role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [RoleManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [RoleManagementController::class, 'updateRole'])->name('users.update-role');
});

// Student Portal Routes (Protected by role:student)
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/literacy', [StudentPortalController::class, 'literacy'])->name('literacy');
    Route::get('/wellbeing', [StudentPortalController::class, 'wellbeing'])->name('wellbeing');
    Route::post('/wellbeing', [StudentPortalController::class, 'storeWellbeing'])->name('wellbeing.store');
    Route::post('/mood', [StudentPortalController::class, 'storeMood'])->name('mood.store');
    Route::get('/counselling', [StudentPortalController::class, 'counselling'])->name('counselling');
    Route::post('/counselling', [StudentPortalController::class, 'storeCounselling'])->name('counselling.store');
    Route::get('/schedule', [StudentPortalController::class, 'counselling'])->name('schedule');
    Route::get('/notifications', [StudentPortalController::class, 'notifications'])->name('notifications');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
