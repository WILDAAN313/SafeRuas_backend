<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PenugasanController;
use App\Http\Controllers\Admin\StatistikController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\superadmin\AdminInstansiController;
use App\Http\Controllers\superadmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\superadmin\LaporanController as SuperAdminLaporanController;
use App\Http\Controllers\superadmin\MasyarakatController;
use App\Http\Controllers\superadmin\PetaLaporanController;
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
    });

Route::middleware(['auth', 'admin.instansi'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
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

Route::middleware(['auth', 'super.admin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('admin-instansi', AdminInstansiController::class)
            ->parameters(['admin-instansi' => 'user'])
            ->names('admin_instansi')
            ->where(['user' => '[0-9]+']);
        Route::get('/masyarakat', [MasyarakatController::class, 'index'])->name('masyarakat.index');
        Route::get('/masyarakat/{user}', [MasyarakatController::class, 'show'])
            ->whereNumber('user')->name('masyarakat.show');
        Route::get('/peta-laporan', PetaLaporanController::class)->name('laporan.peta');
        Route::get('/laporan', [SuperAdminLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/{laporan}', [SuperAdminLaporanController::class, 'show'])
            ->whereNumber('laporan')
            ->name('laporan.show');
    });
