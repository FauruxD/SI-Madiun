<?php

use App\Http\Controllers\Admin\DesaController;
use App\Http\Controllers\Admin\KabupatenController;
use App\Http\Controllers\Admin\KecamatanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): RedirectResponse => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('/lupa-kata-sandi', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/wilayah/desa/{desa}', [DesaController::class, 'show'])
        ->whereNumber('desa')
        ->name('desa.show');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:'.UserRole::SuperAdmin->value)
        ->group(function (): void {
            Route::resource('users', UserController::class)->except(['show', 'destroy']);
            Route::resource('kabupaten', KabupatenController::class)->except('show');
            Route::resource('kecamatan', KecamatanController::class)->except('show');
            Route::resource('desa', DesaController::class)->except('show');
        });
});
