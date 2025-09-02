<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\TripCreationController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\StepController;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\PublicTripController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes publiques

Route::prefix('voyages')->name('public.trips.')->group(function () {
    Route::get('/', [PublicTripController::class, 'index'])->name('index');
    Route::get('/{trip}', [PublicTripController::class, 'show'])->name('show');
});

// Routes où l'utilisateur doit être vérifié

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    // Routes pour créer la 1ère partie de la création du voyage
    Route::get('/trips/start', [TripCreationController::class, 'start'])->name('trips.start');
    Route::post('/trips/start', [TripCreationController::class, 'store'])->name('trips.start.store');
//  CRUD pour trips
    Route::resource('trips', TripController::class);
    // CRUD pour steps
    Route::resource('trips.steps', StepController::class)->shallow();
    // Duplication d'une étape
    Route::post('/steps/{step}/duplicate', [StepController::class, 'duplicate'])->name('steps.duplicate');
    // Logements liés à une étape
    Route::resource('steps.accommodations', AccommodationController::class)->shallow();


});

Route::controller(GoogleAuthController::class)->group(function () {
    Route::get('/auth/google/redirect','redirect')->name('auth.google.redirect');
    Route::get('/auth/google/callback', 'callback')->name('auth.google.callback');
});

// Routes pour réoargniser l'ordre des étapes
Route::patch('/steps/{step}/move-up', [StepController::class, 'moveUp'])->name('steps.move-up');
Route::patch('/steps/{step}/move-down', [StepController::class, 'moveDown'])->name('steps.move-down');




/*
// Route to redirect to Google's OAuth page
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');

// Route to handle the callback from Google
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
*/
