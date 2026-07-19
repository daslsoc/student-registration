<?php

use App\Http\Controllers\Api\ChangesController;
use App\Http\Controllers\Api\ParentsController;
use App\Http\Middleware\VerifyApiToken;
use Illuminate\Support\Facades\Route;

/*
| Integration API. Every route here is behind VerifyApiToken (a per-consumer
| Bearer token) and lightly throttled. It exposes family PII, so nothing here
| may be public.
*/

Route::middleware([VerifyApiToken::class, 'throttle:60,1'])->group(function () {
    // Paid students + their class allocations — consumed by student-attendance.
    Route::get('/integration/changes', [ChangesController::class, 'index'])
        ->name('api.integration.changes');

    // Parent/guardian contacts — consumed by tea-roster for the tea roster.
    Route::get('/integration/parents', [ParentsController::class, 'index'])
        ->name('api.integration.parents');
});
