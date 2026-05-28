<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('visits/export/csv', [VisitController::class, 'exportCsv'])
         ->name('visits.export.csv');

    Route::get('visits/export/pdf', [VisitController::class, 'exportPdf'])
         ->name('visits.export.pdf');

    Route::resource('visits', VisitController::class);

    Route::resource('students', StudentController::class);

    Route::resource('medicines', MedicineController::class);
});
