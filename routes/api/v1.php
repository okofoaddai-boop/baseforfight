<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\ClubController;
use App\Http\Controllers\Api\V1\ClubInvitationController;
use App\Http\Controllers\Api\V1\ClubMemberController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\FighterController;
use App\Http\Controllers\Api\V1\RegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'service' => 'baseforfight-api',
    'version' => 'v1',
]));

Route::post('/auth/token', [AuthTokenController::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', fn (Request $request) => $request->user());
    Route::delete('/auth/token', [AuthTokenController::class, 'destroy']);

    Route::get('/clubs', [ClubController::class, 'index']);
    Route::post('/clubs', [ClubController::class, 'store']);
    Route::get('/clubs/{club}', [ClubController::class, 'show']);
    Route::patch('/clubs/{club}', [ClubController::class, 'update']);
    Route::get('/clubs/{club}/members', [ClubMemberController::class, 'index']);
    Route::patch('/clubs/{club}/members/{user}', [ClubMemberController::class, 'update']);
    Route::delete('/clubs/{club}/members/{user}', [ClubMemberController::class, 'destroy']);
    Route::get('/clubs/{club}/invitations', [ClubInvitationController::class, 'index']);
    Route::post('/clubs/{club}/invitations', [ClubInvitationController::class, 'store']);
    Route::post('/clubs/invitations/accept', [ClubInvitationController::class, 'accept']);

    Route::get('/fighters', [FighterController::class, 'index']);
    Route::post('/fighters', [FighterController::class, 'store']);
    Route::get('/fighters/{fighter}', [FighterController::class, 'show']);
    Route::patch('/fighters/{fighter}', [FighterController::class, 'update']);

    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::patch('/events/{event}', [EventController::class, 'update']);
    Route::post('/events/{event}/cancel', [EventController::class, 'cancel']);

    Route::get('/registrations', [RegistrationController::class, 'index']);
    Route::post('/registrations', [RegistrationController::class, 'store']);
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show']);
    Route::patch('/registrations/{registration}', [RegistrationController::class, 'update']);
    Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy']);
});
