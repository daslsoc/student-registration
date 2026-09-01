<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [HomeController::class, 'home']);
Route::get('/guidelines', [HomeController::class, 'guidelines'])->name('guidelines');
Route::get('/help', [HelpController::class, 'parent'])->name('help');
Route::get('/registration', [RegistrationController::class, 'showRegistrationForm'])->name('registration.form');
Route::post('/registration', [RegistrationController::class, 'handleRegistration'])
    ->middleware('throttle:10,1')
    ->name('registration.submit');
Route::get('/registration/success/{parent}', [RegistrationController::class, 'handleSuccess'])->name('registration.success');

Route::get('/registration/retrieve', [RegistrationController::class, 'showRetrieveDetailsForm'])->name('registration.retrieve');
Route::post('/registration/retrieve', [RegistrationController::class, 'sendUpdateLink'])
    ->middleware('throttle:5,1') // sends email; throttle to limit abuse / enumeration
    ->name('registration.retrieve.send');
Route::get('/registration/update/{token}', [RegistrationController::class, 'showUpdateForm'])->name('registration.update');
Route::post('/registration/update/{token}', [RegistrationController::class, 'handleUpdate'])->name('registration.update.submit');

// Admin. Every route also carries EnsureUserIsActive so a deactivated account
// is thrown out on its next request rather than at its next login, and an atom
// from config/permissions.php saying what the page needs.
Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::get('/admin/parents-students', [AdminController::class, 'showParentStudentList'])
        ->middleware('can:view_registrations')->name('admin.parent_student_list');
    Route::get('/admin/export-csv', [AdminController::class, 'exportCsv'])
        ->middleware('can:export_registrations')->name('admin.export_csv');
    Route::get('/admin/import-csv', [RegistrationController::class, 'showImportCsvForm'])
        ->middleware('can:import_registrations')->name('admin.show_import_csv');
    Route::post('/admin/import-csv', [RegistrationController::class, 'handleCsvImport'])
        ->middleware('can:import_registrations')->name('admin.import_csv');
    Route::get('/admin/allergies', [AdminController::class, 'showAllergies'])
        ->middleware('can:view_allergies')->name('admin.allergies');
    Route::get('/admin/unallocated', [AdminController::class, 'showUnallocated'])
        ->middleware('can:view_unallocated')->name('admin.unallocated');
    Route::get('/admin/class-relocation', [AdminController::class, 'searchRelocation'])
        ->middleware('can:manage_allocations')->name('admin.class_relocation');
    Route::post('/admin/allocations', [AdminController::class, 'updateAllocations'])
        ->middleware('can:manage_allocations')->name('admin.allocations.update');

    Route::get('/admin/payment-override', [AdminController::class, 'showPaymentOverride'])
        ->middleware('can:manage_payment_overrides')->name('admin.payment_override');
    Route::post('/admin/payment-override', [AdminController::class, 'storePaymentOverride'])
        ->middleware('can:manage_payment_overrides')->name('admin.payment_override.store');

    // User accounts. Only a role carrying manage_users gets in here.
    Route::middleware('can:manage_users')->group(function () {
        Route::get('/admin/users', [UserAdminController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [UserAdminController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserAdminController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [UserAdminController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [UserAdminController::class, 'update'])->name('admin.users.update');
        // "Remove" is a deactivation, so it's a POST, not a DELETE.
        Route::post('/admin/users/{user}/deactivate', [UserAdminController::class, 'deactivate'])->name('admin.users.deactivate');
        Route::post('/admin/users/{user}/reactivate', [UserAdminController::class, 'reactivate'])->name('admin.users.reactivate');
    });

    // Roles and their permissions.
    Route::middleware('can:manage_roles')->group(function () {
        Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles.index');
        Route::get('/admin/roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
        Route::post('/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
        Route::get('/admin/roles/{role}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
        Route::put('/admin/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/admin/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
    });

    Route::get('/admin/audit', [ActivityLogController::class, 'index'])
        ->middleware('can:view_audit_log')->name('admin.audit');

    Route::get('/admin/help', [HelpController::class, 'admin'])->name('admin.help');
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

// Forgot / reset password. The route NAMES matter: Laravel's built-in reset
// notification builds its link from 'password.reset'.
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    // Sends email; throttled so it can't be used to spam an inbox.
    ->middleware('throttle:5,1')
    ->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->middleware('throttle:10,1')
    ->name('password.update');
