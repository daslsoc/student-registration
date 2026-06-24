<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [HomeController::class, 'home']);
Route::get('/guidelines', [HomeController::class, 'guidelines'])->name('guidelines');
Route::get('/registration', [RegistrationController::class, 'showRegistrationForm'])->name('registration.form');
Route::post('/registration', [RegistrationController::class, 'handleRegistration'])
    ->middleware('throttle:10,1')
    ->name('registration.submit');
Route::get('/registration/success/{parent}', [RegistrationController::class, 'handleSuccess'])->name('registration.success');

Route::get('/registration/retrieve', [RegistrationController::class, 'showRetrieveDetailsForm'])->name('registration.retrieve');
Route::post('/registration/retrieve', [RegistrationController::class, 'sendUpdateLink'])->name('registration.retrieve.send');
Route::get('/registration/update/{token}', [RegistrationController::class, 'showUpdateForm'])->name('registration.update');
Route::post('/registration/update/{token}', [RegistrationController::class, 'handleUpdate'])->name('registration.update.submit');

// Admin
Route::middleware('auth')->group(function () {
    Route::get('/admin/orientation', [AdminController::class, 'showOrientationList'])->name('admin.orientation_list');
    Route::get('/admin/parents-students', [AdminController::class, 'showParentStudentList'])->name('admin.parent_student_list');
    Route::get('/admin/export-csv', [AdminController::class, 'exportCsv'])->name('admin.export_csv');
    Route::get('/admin/import-csv', [RegistrationController::class, 'showImportCsvForm'])->name('admin.show_import_csv');
    Route::post('/admin/import-csv', [RegistrationController::class, 'handleCsvImport'])->name('admin.import_csv');
});

// Show the login form (GET)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Process login submission (POST). Throttled to slow credential stuffing.
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('login.submit');

// Process logout (POST).
// In many Laravel apps, it's a POST route for CSRF protection, although some do GET.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
