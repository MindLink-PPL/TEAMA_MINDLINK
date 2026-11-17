
<?php
use App\Http\Controllers\CounsellorController;
use App\Http\Controllers\CounsellingController;

use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Counsellor\CounsellorPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\StudentPortalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Admin\CounsellorVerificationController;
use App\Http\Controllers\InformedConsentController;

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
    return response()->file(base_path('index.html'));
});

// Counselling Request
// counselling request student side
Route::get('/counselling/request', [CounsellingController::class, 'create'])->name('counselling.request');
Route::post('/counselling/request', [CounsellingController::class, 'store'])->name('counselling.store');

// counsellor dashboard 
Route::get('/counsellor/dashboard', [CounsellorController::class, 'index'])->name('counsellor.dashboard');
Route::patch('/counsellor/requests/{id}/status', [CounsellorController::class, 'updateStatus'])->name('counsellor.updateStatus');
//
// Informed Consent
Route::get('/test-consent', function () {
    return view('consent');
});

Route::post('/submit-consent', [InformedConsentController::class, 'store']);
Route::get('/admin/consents', [InformedConsentController::class, 'index']);

//
Route::get('/{any}', function () {
    return response()->file(base_path('index.html'));
})->where('any', '.*');


// Role Based Access
Route::get('/dashboard', function () {
    $user = auth()->user()->load('role.permissions');
    $upcomingAppointment = null;
    $latestMood = null;
    $counsellorData = [];

    if ($user->isStudent()) {
        $upcomingAppointment = $user->counsellingRequests()->latest()->first();
        $latestMood = $user->wellbeingCheckins()->whereNotNull('mood')->latest()->value('mood');
    } elseif ($user->isCounsellor()) {
        $counsellorData = CounsellorPortalController::getWorkspaceData();
    }

    return view('dashboard', array_merge(
        compact('user', 'upcomingAppointment', 'latestMood'),
        $counsellorData
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

// Administrator Routes (Protected by role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [RoleManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [RoleManagementController::class, 'updateRole'])->name('users.update-role');
});

// Counsellor Portal Routes (Protected by role:counsellor)
Route::middleware(['auth', 'role:counsellor'])->prefix('counsellor')->name('counsellor.')->group(function () {
    Route::get('/workspace', [CounsellorPortalController::class, 'workspace'])->name('workspace');
    Route::get('/requests', [CounsellorPortalController::class, 'requests'])->name('requests');
    Route::patch('/requests/{counsellingRequest}', [CounsellorPortalController::class, 'updateRequest'])->name('requests.update');
    Route::post('/notes', [CounsellorPortalController::class, 'storeSessionNote'])->name('notes.store');
    Route::get('/schedule', [CounsellorPortalController::class, 'schedule'])->name('schedule');
    Route::get('/records', [CounsellorPortalController::class, 'records'])->name('records');
    Route::get('/referrals', [CounsellorPortalController::class, 'referrals'])->name('referrals');
    Route::post('/referrals', [CounsellorPortalController::class, 'storeReferral'])->name('referrals.store');
    Route::patch('/referrals/{referral}', [CounsellorPortalController::class, 'updateReferral'])->name('referrals.update');
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
