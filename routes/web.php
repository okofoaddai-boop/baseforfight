<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BoxSettingsController;
use App\Http\Controllers\Admin\ClubManagementController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\LegacyMigrationController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Web\ClubMembershipController;
use App\Http\Controllers\Web\ClubPortalController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ProfileController;
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
Route::get('/bff-leicht-erklaert', [PageController::class, 'show'])->defaults('page', 'explained')->name('pages.explained');
Route::get('/preise', [PageController::class, 'show'])->defaults('page', 'pricing')->name('pages.pricing');
Route::get('/datenschutz', [PageController::class, 'show'])->defaults('page', 'privacy')->name('pages.privacy');
Route::get('/impressum', [PageController::class, 'show'])->defaults('page', 'imprint')->name('pages.imprint');
Route::get('/events/{event}', [PublicEventController::class, 'show'])->name('events.show');
Route::get('/events/{event}/documents/{documentIndex}', [PublicEventController::class, 'showDocument'])->name('events.documents.show');
Route::get('/clubs/{club:slug}', [ClubPortalController::class, 'show'])->name('clubs.show');
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('auth')->group(function (): void {
    Route::get('/my/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/my/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Club portal actions
    Route::patch('/clubs/{club:slug}', [ClubPortalController::class, 'update'])->name('clubs.update');
    Route::post('/clubs/{club:slug}/fighters', [ClubPortalController::class, 'storeFighter'])->name('clubs.fighters.store');
    Route::patch('/clubs/{club:slug}/fighters/{fighter}', [ClubPortalController::class, 'updateFighter'])->name('clubs.fighters.update');
    Route::post('/clubs/{club:slug}/events', [ClubPortalController::class, 'storeEvent'])->name('clubs.events.store');
    Route::patch('/clubs/{club:slug}/events/{event}', [ClubPortalController::class, 'updateEvent'])->name('clubs.events.update');
    Route::get('/clubs/{club:slug}/events/{event}/registrations', [ClubPortalController::class, 'eventRegistrations'])->name('clubs.events.registrations');
    Route::post('/clubs/{club:slug}/events/{event}/registrations/manage', [ClubPortalController::class, 'manageEventRegistrations'])->name('clubs.events.registrations.manage');
    Route::post('/clubs/{club:slug}/events/ai/extract', [ClubPortalController::class, 'aiExtractEventFromPdf'])->name('clubs.events.ai.extract');
    Route::post('/clubs/{club:slug}/events/{event}/ai/pairings', [ClubPortalController::class, 'aiSuggestPairings'])->name('clubs.events.ai.pairings');
    Route::post('/events/{event}/registrations/sync', [PublicEventController::class, 'syncRegistrations'])->name('events.registrations.sync');
    Route::post('/events/{event}/registrations/manage', [PublicEventController::class, 'manageRegistrations'])->name('events.registrations.manage');
    Route::get('/events/{event}/registrations/export', [PublicEventController::class, 'exportRegistrations'])->name('events.registrations.export');

    // Club membership management (user-facing)
    Route::get('/my/clubs', [ClubMembershipController::class, 'index'])->name('clubs.membership.index');
    Route::post('/my/clubs/join', [ClubMembershipController::class, 'storeJoinRequest'])->name('clubs.membership.join');
    Route::delete('/my/clubs/join/{clubJoinRequest}', [ClubMembershipController::class, 'cancelJoinRequest'])->name('clubs.membership.cancel');
    Route::post('/my/clubs/create', [ClubMembershipController::class, 'storeClub'])->name('clubs.membership.create-club');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
    Route::post('/register/resend-verification', [RegistrationController::class, 'resendVerificationMail'])->name('register.resend-verification');
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});
Route::get('/register/verify-email/{token}', [RegistrationController::class, 'verifyEmail'])->name('register.verify-email');
Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/admin/impersonation/stop', [ImpersonationController::class, 'stop'])->middleware('auth')->name('admin.impersonation.stop');

Route::prefix('admin')->group(function (): void {
    Route::middleware(['auth', 'superadmin.access'])->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/legacy-sync/dry-run', [LegacyMigrationController::class, 'dryRun'])->name('admin.legacy-sync.dry-run');
        Route::post('/legacy-sync/run', [LegacyMigrationController::class, 'migrate'])->name('admin.legacy-sync.run');
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
        Route::post('/clubs/{club:slug}/members/assign', [ClubManagementController::class, 'assignUserToClub'])->name('admin.clubs.members.assign');
        Route::post('/clubs/{club:slug}/members/{member}/roles', [ClubManagementController::class, 'updateMemberRoles'])->name('admin.clubs.members.update-roles');
        Route::delete('/clubs/{club:slug}/members/{member}', [ClubManagementController::class, 'removeMember'])->name('admin.clubs.members.remove');
    });
});
