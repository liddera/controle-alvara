<?php

use App\Http\Controllers\AlertSettingsController;
use App\Http\Controllers\AlvaraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\OwnerWhatsAppConnectionController;
use App\Http\Controllers\PersonalizacaoController;
use App\Http\Controllers\PublicDocumentoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::view('/landing', 'welcome')->name('landing');

Route::get('/public/documentos/{documento}', [PublicDocumentoController::class, 'show'])
    ->middleware('signed')
    ->name('public.documentos.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::resource('empresas', EmpresaController::class);
    Route::resource('alvaras', AlvaraController::class);
    Route::post('/alvaras/{alvara}/enviar-email', [AlvaraController::class, 'enviarEmail'])->name('alvaras.enviar-email');
    Route::post('/alvaras/{alvara}/enviar-whatsapp', [AlvaraController::class, 'enviarWhatsApp'])->name('alvaras.enviar-whatsapp');
    Route::patch('/alvaras/{alvara}/observacoes', [AlvaraController::class, 'updateObservacoes'])->name('alvaras.observacoes.update');
    Route::resource('users', UserController::class)->middleware('plan.limit');
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
    Route::get('/profile/alerts', [AlertSettingsController::class, 'index'])->name('profile.alerts');
    Route::post('/profile/alerts', [AlertSettingsController::class, 'store'])->name('profile.alerts.store');
    Route::delete('/profile/alerts/{config}', [AlertSettingsController::class, 'destroy'])->name('profile.alerts.destroy');
    Route::get('/google/redirect', [GoogleCalendarController::class, 'redirect'])->name('google.redirect');
    Route::delete('/google/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.disconnect');

    // WhatsApp Gateway (Owner connection)
    Route::post('/profile/whatsapp/connect', [OwnerWhatsAppConnectionController::class, 'connect'])->name('whatsapp.connect');
    Route::post('/profile/whatsapp/refresh', [OwnerWhatsAppConnectionController::class, 'refresh'])->name('whatsapp.refresh');
    Route::delete('/profile/whatsapp/disconnect', [OwnerWhatsAppConnectionController::class, 'disconnect'])->name('whatsapp.disconnect');

    // Mark Notifications as Read
    Route::post('/notifications/mark-as-read', function () {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    })->name('notifications.mark-as-read');

    Route::get('/notifications/{notification}/read', [AlertSettingsController::class, 'readAndRedirect'])->name('notifications.read');

    // Personalization & Profile Photo
    Route::get('/profile/personalization', [PersonalizacaoController::class, 'index'])->name('profile.personalization');
    Route::post('/profile/personalization', [PersonalizacaoController::class, 'updateSettings'])->name('profile.personalization.update');
    Route::delete('/profile/personalization/logo', [PersonalizacaoController::class, 'destroyLogo'])->name('profile.personalization.logo.destroy');
    Route::delete('/profile/personalization/header-logo', [PersonalizacaoController::class, 'destroyHeaderLogo'])->name('profile.personalization.header-logo.destroy');
    Route::delete('/profile/personalization/sidebar-compact-logo', [PersonalizacaoController::class, 'destroySidebarCompactLogo'])->name('profile.personalization.sidebar-compact-logo.destroy');
    Route::delete('/profile/personalization/favicon', [PersonalizacaoController::class, 'destroyFavicon'])->name('profile.personalization.favicon.destroy');

    Route::post('/profile/photo', [PersonalizacaoController::class, 'updateProfilePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [PersonalizacaoController::class, 'destroyProfilePhoto'])->name('profile.photo.destroy');
});

Route::get('/google/callback', [GoogleCalendarController::class, 'callback'])->name('google.callback');

// Admin Routes (SaaS) are now handled by Filament at /admin

require __DIR__.'/auth.php';
