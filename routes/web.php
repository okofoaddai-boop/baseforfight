<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BoxSettingsController;
use App\Http\Controllers\Admin\ClubManagementController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Web\ClubPortalController;
use App\Http\Controllers\Web\PublicEventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PublicEventController::class, 'index'])->name('welcome');
Route::get('/events/{event}', [PublicEventController::class, 'show'])->name('events.show');
Route::get('/events/{event}/documents/{documentIndex}', [PublicEventController::class, 'showDocument'])->name('events.documents.show');
Route::get('/clubs/{club:slug}', [ClubPortalController::class, 'show'])->name('clubs.show');
Route::middleware('auth')->group(function (): void {
    Route::patch('/clubs/{club:slug}', [ClubPortalController::class, 'update'])->name('clubs.update');
    Route::post('/clubs/{club:slug}/fighters', [ClubPortalController::class, 'storeFighter'])->name('clubs.fighters.store');
    Route::patch('/clubs/{club:slug}/fighters/{fighter}', [ClubPortalController::class, 'updateFighter'])->name('clubs.fighters.update');
    Route::post('/clubs/{club:slug}/events', [ClubPortalController::class, 'storeEvent'])->name('clubs.events.store');
    Route::patch('/clubs/{club:slug}/events/{event}', [ClubPortalController::class, 'updateEvent'])->name('clubs.events.update');
    Route::post('/clubs/{club:slug}/events/ai/extract', [ClubPortalController::class, 'aiExtractEventFromPdf'])->name('clubs.events.ai.extract');
    Route::post('/clubs/{club:slug}/events/{event}/ai/pairings', [ClubPortalController::class, 'aiSuggestPairings'])->name('clubs.events.ai.pairings');
    Route::post('/events/{event}/registrations/sync', [PublicEventController::class, 'syncRegistrations'])->name('events.registrations.sync');
});
Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
});
Route::get('/register/verify-email/{token}', [RegistrationController::class, 'verifyEmail'])->name('register.verify-email');
Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/admin/impersonation/stop', [ImpersonationController::class, 'stop'])->middleware('auth')->name('admin.impersonation.stop');

Route::prefix('admin')->group(function (): void {
    Route::middleware(['auth', 'superadmin.access'])->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/impersonate/{user}', [ImpersonationController::class, 'switchTo'])->name('admin.impersonate.switch');
        Route::post('/impersonate/club/{club:slug}/{role}', [ImpersonationController::class, 'switchToClubRole'])->name('admin.impersonate.club-role');
        Route::get('/modules', [ModuleController::class, 'index'])->name('admin.modules.index');
        Route::post('/modules/{module}/toggle', [ModuleController::class, 'toggle'])->name('admin.modules.toggle');
        Route::post('/modules/ai/settings', [ModuleController::class, 'updateAiSettings'])->name('admin.modules.ai.settings.update');
        Route::get('/box-settings/{section?}', [BoxSettingsController::class, 'index'])->name('admin.boxing.settings.index');
        Route::post('/box-settings/{section}', [BoxSettingsController::class, 'update'])->name('admin.boxing.settings.update');
    });

    Route::middleware(['auth', 'admin.access'])->group(function (): void {
        Route::get('/clubs', [ClubManagementController::class, 'index'])->name('admin.clubs.index');
        Route::post('/club-join-requests/{clubJoinRequest}/approve', [ClubManagementController::class, 'approveJoinRequest'])->name('admin.club-join-requests.approve');
        Route::post('/club-join-requests/{clubJoinRequest}/decline', [ClubManagementController::class, 'declineJoinRequest'])->name('admin.club-join-requests.decline');
    });
});
