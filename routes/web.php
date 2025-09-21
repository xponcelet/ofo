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
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ActivityController;

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
    // voyage aléatoire
    Route::get('/aleatoire', [PublicTripController::class, 'random'])->name('random');
    // Détail d'un voyage
    Route::get('/', [PublicTripController::class, 'index'])->name('index');
    Route::get('/{trip}', [PublicTripController::class, 'show'])->name('show');

});
// dashboard public (séparé de /dashboard authentifié)
Route::get('/decouvrir', [PublicTripController::class, 'dashboard'])->name('public.dashboard');

// Routes où l'utilisateur doit être vérifié

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard user authentifié
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Création de voyage (3 étapes)
    Route::prefix('trips')->name('trips.')->group(function () {
        // Étape 1 : Destination
        Route::get('/destination', [TripCreationController::class, 'destination'])->name('destination');
        Route::post('/destination', [TripCreationController::class, 'storeDestination'])->name('destination.store');
        // Étape 2 : Point de départ
        Route::get('/start', [TripCreationController::class, 'start'])->name('start');
        Route::post('/start', [TripCreationController::class, 'store'])->name('start.store');
        // Étape 3 : Détails + création finale
        Route::get('/details', [TripCreationController::class, 'details'])->name('details');
        Route::post('/details', [TripCreationController::class, 'finalize'])->name('details.store');
    });

    //  CRUD pour trips
    Route::resource('trips', TripController::class);
    // CRUD pour steps
    Route::resource('trips.steps', StepController::class)->shallow();
    // Duplication d'une étape
    Route::post('/steps/{step}/duplicate', [StepController::class, 'duplicate'])->name('steps.duplicate');
    // Logements liés à une étape
    Route::resource('steps.accommodations', AccommodationController::class)->shallow();
    // Gestion des favoris
    Route::post('/trips/{trip}/favorite', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/trips/{trip}/favorite', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    // liste des favoris
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    // CRUD des activités liées à une étape
    Route::resource('steps.activities', ActivityController::class)->shallow();

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
