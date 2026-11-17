<?php

use App\Http\Controllers\Admin\AdminArticleController;
use App\Http\Controllers\Admin\CounsellorVerificationController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\AdminQuestionnaireController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CounsellingController;
use App\Http\Controllers\Counsellor\CounsellorPortalController;
use App\Http\Controllers\CounsellorController;
use App\Http\Controllers\InformedConsentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\Student\StudentPortalController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', function () {
    return view('welcome');
});

// Student Wellbeing Self Check-in CRUD Routes (PBI #15, #16, #17)
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

// Mental Health Literacy (Public/Student Article Reader - PBI #21, #22, #23)
Route::get('/learn', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/learn/saved', [ArticleController::class, 'saved'])->name('articles.saved');
Route::get('/learn/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::post('/learn/{article}/save', [ArticleController::class, 'toggleSave'])->name('articles.toggleSave');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('articles', AdminArticleController::class)->except(['show']);
});

// Counsellor Registration (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/counsellor/register', [AuthController::class, 'showCounsellorRegister'])
        ->name('counsellor.register');
    Route::post('/counsellor/register', [AuthController::class, 'registerCounsellor'])
        ->name('counsellor.register.submit');
});

// Student Appointment Scheduling & APIs
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/appointments', [AppointmentController::class, 'page'])->name('appointments');
    });

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

// Counsellor Verification & Availability Management
Route::middleware(['auth', 'role:counsellor'])
    ->prefix('counsellor')
    ->name('counsellor.')
    ->group(function () {
        Route::get('/verification', [AuthController::class, 'counsellorVerificationStatus'])
            ->name('verification');
        Route::post('/availability', [AppointmentController::class, 'storeAvailability'])
            ->name('availability.store');
        Route::delete('/availability/{availability}', [AppointmentController::class, 'destroyAvailability'])
            ->name('availability.destroy');
    });

// Admin Counsellor Verification Routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [CounsellorVerificationController::class, 'index'])->name('dashboard');
        Route::get('/counsellors/{user}/certificate', [CounsellorVerificationController::class, 'certificate'])->name('counsellors.certificate');
        Route::post('/counsellors/{user}/approve', [CounsellorVerificationController::class, 'approve'])->name('counsellors.approve');
        Route::post('/counsellors/{user}/reject', [CounsellorVerificationController::class, 'reject'])->name('counsellors.reject');
    });

// Counselling Request Wizard & Counsellor Review Dashboard
Route::get('/counselling/request', [CounsellingController::class, 'create'])->name('counselling.request');
Route::post('/counselling/request', [CounsellingController::class, 'store'])->name('counselling.store');
Route::get('/counsellor/dashboard', [CounsellorController::class, 'index'])->name('counsellor.dashboard');
Route::patch('/counsellor/requests/{id}/status', [CounsellorController::class, 'updateStatus'])->name('counsellor.updateStatus');

// Informed Consent (PBI #6, #7, #8)
Route::get('/test-consent', function () {
    return view('consent');
});
Route::post('/submit-consent', [InformedConsentController::class, 'store']);
Route::get('/admin/consents', [InformedConsentController::class, 'index']);

// Role-Based Portal Dashboard (PG-01 Student, PG-05 Counsellor, PG-06 Admin)
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

// Administrator Role Management Routes (Protected by role:admin)
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

// User Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';