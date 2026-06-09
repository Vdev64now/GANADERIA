<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\CattleController;
use App\Http\Controllers\SlaughterController;
use App\Http\Controllers\DeboningController;
use App\Http\Controllers\CutTypeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SlaughterhouseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoginController;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // Dashboard & Filters
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/set-farm/{id?}', [DashboardController::class, 'setFarm'])->name('set-farm');

    // Hacienda / Farm CRUD
    Route::resource('farms', FarmController::class)->except(['show']);

    // Cattle CRUD
    Route::resource('cattle', CattleController::class)->except(['show']);

    // Slaughters CRUD (Beneficios)
    Route::resource('slaughters', SlaughterController::class)->except(['show', 'edit', 'update']);

    // Debonings CRUD (Despostes)
    Route::resource('debonings', DeboningController::class)->except(['show', 'edit', 'update']);

    // Cut Types & Settings
    Route::resource('cuts', CutTypeController::class)->except(['show', 'create', 'edit']);
    Route::post('cuts/settings', [CutTypeController::class, 'updateSettings'])->name('cuts.settings.update');

    // Sales CRUD (Ventas)
    Route::resource('sales', SaleController::class)->except(['show', 'edit', 'update']);

    // Slaughterhouse CRUD (Mataderos)
    Route::resource('slaughterhouses', SlaughterhouseController::class)->except(['show']);

    // Customer CRUD (Clientes)
    Route::resource('customers', CustomerController::class)->except(['show']);
});

