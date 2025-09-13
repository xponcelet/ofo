<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Access\AuthorizationException;

class TripController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(Request $request): \Inertia\Response
    {
        $user = auth()->user();

        $perPage = (int) $request->integer('per_page', 12);

        $trips = Trip::query()
            ->select(['id','title','description','image','start_date','end_date'])
            ->where('user_id', $user->id)
            ->withCount(['steps','favoredBy as favs'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        // ✅ Compte frais depuis la DB (évite tout cache/état relationnel)
        $count = Trip::where('user_id', $user->id)->count();
        $max   = $user->tripLimit();

        return \Inertia\Inertia::render('Trips/Index', [
            'trips' => $trips,
            'limits' => [
                'max'   => $max,
                'count' => $count,
            ],
            'can' => [
                'create_trip' => $count < $max, // ✅ source de vérité pour l’UI
            ],
        ]);
    }


    public function create(): InertiaResponse
    {
        // Pré-check UX : on inspecte la policy sans lever d’exception
        $response = Gate::inspect('create', Trip::class);

        if (! $response->allowed()) {
            return Inertia::render('Trips/Index', [
                // Tu peux aussi rediriger vers index() si tu préfères
                'trips' => Trip::where('user_id', auth()->id())
                    ->select(['id','title','description','image','start_date','end_date'])
                    ->withCount(['steps','favoredBy as favs'])
                    ->latest()->paginate(12),
                'limits' => [
                    'max'   => auth()->user()->tripLimit(),
                    'count' => auth()->user()->trips()->count(),
                ],
                'errors' => [
                    'base' => __('Vous avez atteint la limite de :max voyages.', [
                        'max' => auth()->user()->tripLimit(),
                    ]),
                ],
            ]);
        }

        return Inertia::render('Trips/Create');
    }

    public function store(StoreTripRequest $request)
    {
        $user = auth()->user();

        // Verrou anti double-clic / onglets concurrents (5s)
        $lock = Cache::lock("user:{$user->id}:create-trip", 5);

        return $lock->block(5, function () use ($request, $user) {
            try {
                // Sécurité finale : policy “create”
                $this->authorize('create', Trip::class);
            } catch (AuthorizationException $e) {
                // UX friendly : transformer en message de formulaire
                return back()->withErrors([
                    'base' => __('Vous avez atteint la limite de :max voyages.', [
                        'max' => $user->tripLimit(),
                    ]),
                ])->onlyInput();
            }

            $validated = $request->validated();

            $trip = Trip::create([
                'user_id'     => $user->id,
                'title'       => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
                'image'       => null, // gère l’upload si nécessaire
                'start_date'  => $validated['start_date'] ?? null,
                'end_date'    => $validated['end_date'] ?? null,
                'budget'      => $validated['budget'] ?? null,
                'currency'    => $validated['currency'] ?? 'EUR',
                'is_public'   => (bool) ($validated['is_public'] ?? false),
            ]);

            // À toi de choisir : edit (pour compléter) ou show
            return redirect()
                ->route('trips.edit', $trip) // ou ->route('trips.show', $trip)
                ->with('success', __('Voyage créé !'));
        });
    }

    public function show(Trip $trip): InertiaResponse
    {
        $this->authorize('view', $trip);

        // 1) Compteur de likes
        $trip->loadCount(['favoredBy as favs']);

        // 2) Likers (seulement pour le propriétaire)
        $likers = null;
        if (auth()->check() && auth()->user()->can('viewLikers', $trip)) {
            $likers = $trip->favoredBy()
                ->select('users.id','users.name','users.email')
                ->withPivot('created_at')
                ->orderByDesc('favorites.created_at')
                ->paginate(12)
                ->withQueryString();
        }

        // 3) Étapes + activités (+ logements si utilisé)
        $trip->load([
            'steps' => function ($q) {
                $q->orderBy('order')
                    ->select('id','trip_id','order','title','location','start_date','end_date','latitude','longitude');
            },
            'steps.activities' => function ($q) {
                $q->orderBy('start_at')
                    ->select('id','step_id','title','description','start_at','end_at','external_link','cost','currency','category');
            },
            'steps.accommodations' => function ($q) {
                $q->select('id','step_id','title','location','start_date','end_date');
            },
        ]);

        return Inertia::render('Trips/Show', [
            'trip'   => $trip,
            'steps'  => $trip->steps,
            'favs'   => $trip->favs,
            'likers' => $likers,
        ]);
    }

    public function edit(Trip $trip): InertiaResponse
    {
        $this->authorize('update', $trip);

        return Inertia::render('Trips/Edit', [
            'trip' => $trip,
        ]);
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $trip->update($request->validated());

        return redirect()
            ->route('trips.show', $trip)
            ->with('success', __('Voyage mis à jour avec succès.'));
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);
        $trip->delete();

        return redirect()->route('trips.index')->with('success', 'Voyage supprimé.');
    }

}
