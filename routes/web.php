<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PenugasanController;
use App\Http\Controllers\Admin\StatistikController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'create'])->name('home');

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'create')->name('login');
    Route::post('/login', 'store')->middleware('throttle:5,1');
    Route::post('/logout', 'destroy')->middleware('auth')->name('logout');
});

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
//     });

// Route::middleware(['auth', 'admin.instansi'])
//     ->prefix('admin')
//     ->name('admin.')
//     ->group(function () {

        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan.index');

        Route::get('/laporan/{id}', [LaporanController::class, 'show'])
            ->whereNumber('id')
            ->name('laporan.show');

        Route::patch('/laporan/{id}/status', [LaporanController::class, 'updateStatus'])
            ->whereNumber('id')
            ->name('laporan.status.update');

        Route::get('/penugasan', [PenugasanController::class, 'index'])
            ->name('penugasan.index');

        Route::post('/penugasan/{id}', [PenugasanController::class, 'store'])
            ->whereNumber('id')
            ->name('penugasan.store');

        Route::get('/statistik', [StatistikController::class, 'index'])
            ->name('statistik.index'); 
            });
