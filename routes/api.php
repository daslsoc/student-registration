<?php

use App\Http\Controllers\Api\PaidStudentController;
use App\Http\Middleware\VerifyApiToken;
use Illuminate\Support\Facades\Route;

/*
| Integration API. Every route here is behind VerifyApiToken (Bearer token)
| and lightly throttled. It exposes student PII, so nothing here may be public.
*/

Route::middleware([VerifyApiToken::class, 'throttle:60,1'])->group(function () {
    Route::get('/integration/paid-students', [PaidStudentController::class, 'index'])
        ->name('api.integration.paid-students');
});
