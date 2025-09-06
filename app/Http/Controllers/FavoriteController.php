<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
    public function store(Trip $trip): RedirectResponse
    {
        auth()->user()->favoriteTrips()->syncWithoutDetaching([$trip->id]);

        return back()->with('success', 'Voyage ajouté aux favoris.');
    }

    public function destroy(Trip $trip): RedirectResponse
    {
        auth()->user()->favoriteTrips()->detach($trip->id);

        return back()->with('success', 'Voyage retiré des favoris.');
    }

    public function index(): Response
    {
        $favorites = auth()->user()
            ->favoriteTrips()
            ->select('trips.id', 'trips.title', 'trips.image', 'trips.start_date', 'trips.end_date')
            ->withCount('favoredBy')                 // -> favorites_count
            ->withPivot('created_at')
            ->orderByDesc('favorites.created_at')    // tri par date d’ajout (pivot)
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Favorites/Index', [
            'favorites' => $favorites,
        ]);
    }
}
