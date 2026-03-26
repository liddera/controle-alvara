<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\AlvaraController;
use App\Models\Documento;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $plans = \App\Models\Plan::all();
    return view('welcome', compact('plans'));
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::resource('empresas', EmpresaController::class);
    Route::resource('alvaras', AlvaraController::class);
    Route::post('/alvaras/{alvara}/enviar-email', [AlvaraController::class, 'enviarEmail'])->name('alvaras.enviar-email');
    Route::resource('users', \App\Http\Controllers\UserController::class)->middleware('plan.limit');
    Route::delete('/documentos/{documento}', [AlvaraController::class, 'destroyDocumento'])->name('documentos.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // API Tokens Management
    Route::get('/profile/tokens', [ProfileController::class, 'tokens'])->name('profile.tokens');
    Route::post('/profile/tokens', [ProfileController::class, 'storeToken'])->name('profile.tokens.store');
    Route::delete('/profile/tokens/{tokenId}', [ProfileController::class, 'destroyToken'])->name('profile.tokens.destroy');

    // Alert Settings Management
    Route::get('/profile/alerts', [\App\Http\Controllers\AlertSettingsController::class, 'index'])->name('profile.alerts');
    Route::post('/profile/alerts', [\App\Http\Controllers\AlertSettingsController::class, 'store'])->name('profile.alerts.store');
    Route::delete('/profile/alerts/{config}', [\App\Http\Controllers\AlertSettingsController::class, 'destroy'])->name('profile.alerts.destroy');

    // Mark Notifications as Read
    Route::post('/notifications/mark-as-read', function() {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.mark-as-read');

    Route::get('/notifications/{notification}/read', [\App\Http\Controllers\AlertSettingsController::class, 'readAndRedirect'])->name('notifications.read');

    // Personalization & Profile Photo
    Route::get('/profile/personalization', [\App\Http\Controllers\PersonalizacaoController::class, 'index'])->name('profile.personalization');
    Route::post('/profile/personalization', [\App\Http\Controllers\PersonalizacaoController::class, 'updateSettings'])->name('profile.personalization.update');
    Route::delete('/profile/personalization/logo', [\App\Http\Controllers\PersonalizacaoController::class, 'destroyLogo'])->name('profile.personalization.logo.destroy');
    Route::delete('/profile/personalization/favicon', [\App\Http\Controllers\PersonalizacaoController::class, 'destroyFavicon'])->name('profile.personalization.favicon.destroy');
    
    Route::post('/profile/photo', [\App\Http\Controllers\PersonalizacaoController::class, 'updateProfilePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [\App\Http\Controllers\PersonalizacaoController::class, 'destroyProfilePhoto'])->name('profile.photo.destroy');
});

// Admin Routes (SaaS) are now handled by Filament at /admin

require __DIR__.'/auth.php';
