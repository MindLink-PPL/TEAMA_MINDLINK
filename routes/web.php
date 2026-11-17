
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Admin\CounsellorVerificationController;

// Home redirect
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if ($user->role === 'counsellor') {
        if ($user->verification_status !== 'verified') {
            return redirect()->route('counsellor.verification');
        }

        return redirect()->route('counsellor.dashboard');
    }

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('student.appointments');
});

// Guest routes
Route::middleware('guest')->group(function () {

    // Student login and registration
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.submit');

    // Counsellor registration
    Route::get('/counsellor/register', [AuthController::class, 'showCounsellorRegister'])
        ->name('counsellor.register');

    Route::post('/counsellor/register', [AuthController::class, 'registerCounsellor'])
        ->name('counsellor.register.submit');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Student pages
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        Route::get('/appointments', [AppointmentController::class, 'page'])
            ->name('appointments');
    });

// Student APIs
Route::middleware(['auth', 'role:student'])
    ->prefix('api')
    ->group(function () {

        Route::get('/counsellors', [AppointmentController::class, 'counsellors']);
        Route::get('/slots', [AppointmentController::class, 'slots']);
        Route::get('/appointments', [AppointmentController::class, 'mine']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
    });

// Counsellor pages
Route::middleware(['auth', 'role:counsellor'])
    ->prefix('counsellor')
    ->name('counsellor.')
    ->group(function () {

        // Verification status
        Route::get('/verification', [AuthController::class, 'counsellorVerificationStatus'])
            ->name('verification');

        // Counsellor dashboard
        Route::get('/dashboard', [AppointmentController::class, 'counsellorDashboard'])
            ->name('dashboard');
            Route::post('/availability',
    [AppointmentController::class, 'storeAvailability']
)->name('availability.store');

// Delete availability
Route::delete('/availability/{availability}',
    [AppointmentController::class, 'destroyAvailability']
)->name('availability.destroy');
    });

    // Admin routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard',
            [CounsellorVerificationController::class, 'index']
        )->name('dashboard');

        Route::get('/counsellors/{user}/certificate',
            [CounsellorVerificationController::class, 'certificate']
        )->name('counsellors.certificate');

        Route::post('/counsellors/{user}/approve',
            [CounsellorVerificationController::class, 'approve']
        )->name('counsellors.approve');

        Route::post('/counsellors/{user}/reject',
            [CounsellorVerificationController::class, 'reject']
        )->name('counsellors.reject');
        // Add availability

    });