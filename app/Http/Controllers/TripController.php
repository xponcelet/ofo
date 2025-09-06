<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Inertia\Response;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class TripController extends Controller
{
    use AuthorizesRequests;


    /*
     *  public function index()
    {
        $trips = Trip::where('user_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('Trips/Index', [
            'trips' => $trips,
        ]);
    }
     */
    public function index(Request $request): \Inertia\Response
    {
        $perPage = (int) $request->integer('per_page', 12);

        $trips = Trip::query()
            ->select(['id','title','description','image','start_date','end_date']) // + description si tu veux l’afficher
            ->where('user_id', auth()->id())
            ->withCount(['steps','favoredBy as favs'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return \Inertia\Inertia::render('Trips/Index', [
            'trips' => $trips,
        ]);
    }



    public function create()
    {
        return Inertia::render('Trips/Create');
    }

    public function store(StoreTripRequest $request)
    {
        $validated = $request->validated();

        $trip = Trip::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image' => null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'currency' => $validated['currency'] ?? 'EUR',
            'is_public' => $validated['is_public'] ?? false,
        ]);

        return redirect()->route('trips.show', $trip)->with('success', 'Voyage créé avec succès.');
    }

    public function show(Trip $trip)
    {
        $this->authorize('view', $trip);

        // ce que tu avais déjà
        $trip->load('steps', 'steps.accommodations');

        // 1) compteur de likes
        $trip->loadCount(['favoredBy as favs']);

        // 2) liste des likers (seulement pour le propriétaire)
        $likers = null;
        if (auth()->check() && auth()->user()->can('viewLikers', $trip)) {
            $likers = $trip->favoredBy()
                ->select('users.id','users.name','users.email') // ajoute d'autres champs si tu veux
                ->withPivot('created_at')                       // pour la date d'ajout en favori
                ->orderByDesc('favorites.created_at')
                ->paginate(12)
                ->withQueryString();
        }

        return \Inertia\Inertia::render('Trips/Show', [
            'trip'   => $trip,
            'steps'  => $trip->steps()->select('id','title','location','latitude','longitude')->get(),
            'favs'   => $trip->favs,     // nombre total de likes
            'likers' => $likers,         // paginator ou null
        ]);
    }


    public function edit(Trip $trip)
    {
        $this->authorize('update', $trip);

        return Inertia::render('Trips/Edit', [
            'trip' => $trip,
        ]);
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validated();

        $trip->update($validated);

        return redirect()->route('trips.show', $trip)->with('success', 'Voyage mis à jour avec succès.');
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);

        $trip->delete();

        return redirect()->route('trips.index')->with('success', 'Voyage supprimé.');
    }
}

