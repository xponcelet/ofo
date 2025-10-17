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
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ChecklistItemController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\TranslationController;

// ============================
// Page d’accueil publique
// ============================
Route::get('/', function () {
    return Inertia::render('Public/Dashboard', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'canCreate' => auth()->check(), // ✅ pour DashboardSearch
    ]);
});

// ============================
// Routes publiques des voyages
// ============================
Route::prefix('voyages')->name('public.trips.')->group(function () {
    Route::get('/', [PublicTripController::class, 'index'])->name('index');
    Route::get('/aleatoire', [PublicTripController::class, 'random'])->name('random');
    Route::get('/{trip}', [PublicTripController::class, 'show'])->name('show');
});

// Dashboard public (découverte)
Route::get('/decouvrir', [PublicTripController::class, 'dashboard'])->name('public.dashboard');

// Changement de langue
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Traduction frontend
Route::get('/translations/{locale}', [TranslationController::class, 'index'])
    ->name('translations.index');

// ============================
// Utilisateurs connectés
// ============================
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard user authentifié
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'canCreate' => true, // ✅ connecté → affichage bouton "Créer un voyage"
        ]);
    })->name('dashboard');

    // Création de voyage (3 étapes)
    Route::prefix('trips')->name('trips.')->group(function () {
        Route::get('/destination', [TripCreationController::class, 'destination'])->name('destination');
        Route::post('/destination', [TripCreationController::class, 'storeDestination'])->name('destination.store');

        Route::get('/start', [TripCreationController::class, 'start'])->name('start');
        Route::post('/start', [TripCreationController::class, 'store'])->name('start.store');

        Route::get('/details', [TripCreationController::class, 'details'])->name('details');
        Route::post('/details', [TripCreationController::class, 'finalize'])->name('details.store');
    });

    // CRUD pour trips
    Route::resource('trips', TripController::class);

    // CRUD pour steps
    Route::resource('trips.steps', StepController::class)->shallow();

    // Duplication d'une étape
    Route::post('/steps/{step}/duplicate', [StepController::class, 'duplicate'])->name('steps.duplicate');

    // Logements liés à une étape
    Route::resource('steps.accommodations', AccommodationController::class)->shallow();

    // Favoris
    Route::post('/trips/{trip}/favorite', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/trips/{trip}/favorite', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // Activités liées à une étape
    Route::resource('steps.activities', ActivityController::class)->shallow();

    // Checklist (items) liés à un voyage
    Route::resource('trips.checklist-items', ChecklistItemController::class)
        ->shallow()
        ->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/checklist-items/{checklist_item}/toggle', [ChecklistItemController::class, 'toggle'])
        ->name('checklist-items.toggle');
    Route::post('/trips/{trip}/checklist-items/reorder', [ChecklistItemController::class, 'reorder'])
        ->name('trips.checklist-items.reorder');

    // Stripe
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::get('/billing/cancel', [BillingController::class, 'cancelled'])->name('billing.cancelled');
});

// ============================
// Auth Google
// ============================
Route::controller(GoogleAuthController::class)->group(function () {
    Route::get('/auth/google/redirect', 'redirect')->name('auth.google.redirect');
    Route::get('/auth/google/callback', 'callback')->name('auth.google.callback');
});

// Réorganisation des étapes
Route::patch('/steps/{step}/move-up', [StepController::class, 'moveUp'])->name('steps.move-up');
Route::patch('/steps/{step}/move-down', [StepController::class, 'moveDown'])->name('steps.move-down');
Route::put('/trips/{trip}/steps/reorder', [StepController::class, 'reorder'])->name('steps.reorder');
// Mettre à jour uniquement les nuits (+ recalc end_date)
Route::patch('/steps/{step}/nights', [StepController::class, 'updateNights'])->name('steps.update.nights');
