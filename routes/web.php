<?php

use App\Http\Controllers\admin\PageDesignController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SubpageController;
use App\Http\Controllers\Admin\LayoutSetupController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Načtení dat pomocí PageDesign controlleru
    $designData = app(PageDesignController::class)->index();

    return Inertia::render('Index', array_merge([
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ], $designData));
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('pages', PageController::class);
    Route::resource('subpage', SubpageController::class);
    Route::resource('layout', LayoutSetupController::class);
    Route::resource('page-design', PageDesignController::class);
});

// Dashboard route
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })
    ->name('dashboard');

// Logout route
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('logout');

