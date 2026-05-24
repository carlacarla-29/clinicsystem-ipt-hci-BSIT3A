<?php

/**
 * ============================================================
 *  CLINIC MONITORING SYSTEM — routes/web.php
 * ============================================================
 *
 * GUIDE: How Laravel routing works
 * ---------------------------------
 * 1. Every HTTP request (GET, POST, PUT, DELETE) hits this file first.
 * 2. Laravel reads the routes top-to-bottom and finds the first match.
 * 3. Matched routes are passed through their middleware stack before
 *    the controller method is called.
 *
 * MIDDLEWARE used here:
 *   'auth'  → Requires the user to be logged in.
 *             If not, they are redirected to the named route 'login'.
 *   'guest' → Requires the user to be a GUEST (not logged in).
 *             If already logged in, they are redirected to '/dashboard'.
 *
 * NAMED ROUTES (->name('...')):
 *   Always use named routes so you never hard-code URLs in views/controllers.
 *   Usage in Blade:  <a href="{{ route('visits.index') }}">
 *   Usage in PHP:    return redirect()->route('dashboard');
 *
 * RESOURCE ROUTES (Route::resource):
 *   One line creates all 7 standard CRUD routes automatically:
 *     GET    /visits            → index   (list all visits)
 *     GET    /visits/create     → create  (show create form)
 *     POST   /visits            → store   (save new visit)
 *     GET    /visits/{visit}    → show    (view single visit)
 *     GET    /visits/{visit}/edit → edit  (show edit form)
 *     PUT    /visits/{visit}    → update  (save edits)
 *     DELETE /visits/{visit}    → destroy (delete visit)
 * ============================================================
 */

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GUEST ROUTES
|--------------------------------------------------------------------------
| These routes are only accessible when the user is NOT logged in.
| The 'guest' middleware redirects logged-in users away to the dashboard.
|
| Why POST for login submit? Because GET requests can be cached/bookmarked,
| which is a security risk for form submissions carrying credentials.
*/
Route::middleware('guest')->group(function () {
    // Show the registration page
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // Show the login page
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    // Handle the login form submission
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (require authentication)
|--------------------------------------------------------------------------
| Every route inside this group requires the user to be logged in.
| The 'auth' middleware checks auth status on every request.
| Unauthenticated users are automatically redirected to route('login').
*/
Route::middleware('auth')->group(function () {

    // --- Logout ---
    // POST to prevent CSRF attacks. Never use GET for logout.
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- Dashboard ---
    // The main clinic overview: today's visits, stats, charts.
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ---------------------------------------------------------------
    // VISITS
    // ---------------------------------------------------------------
    // IMPORTANT: Custom routes MUST come BEFORE Route::resource().
    // If resource() is declared first, the {visit} wildcard would
    // swallow '/visits/export/csv' and cause a 404 or model-not-found error.
    // ---------------------------------------------------------------

    // Feature: Export visits to CSV (date-filterable via query string)
    Route::get('visits/export/csv', [VisitController::class, 'exportCsv'])
         ->name('visits.export.csv');

    // Feature: Export visits to PDF
    Route::get('visits/export/pdf', [VisitController::class, 'exportPdf'])
         ->name('visits.export.pdf');

    // Creates all 7 standard CRUD routes for visits
    Route::resource('visits', VisitController::class);

    // ---------------------------------------------------------------
    // STUDENTS
    // Creates all 7 standard CRUD routes for students
    Route::resource('students', StudentController::class);

    // ---------------------------------------------------------------
    // MEDICINES (Inventory management — additional feature)
    // Creates all 7 standard CRUD routes for medicines
    Route::resource('medicines', MedicineController::class);
});
