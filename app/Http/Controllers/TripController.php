<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
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
            ->where('user_id', $user->id)
            ->withCount(['steps','favoredBy as favs'])
            ->with(['steps:id,trip_id,start_date,end_date,nights']) // ⚡ on charge les étapes
            ->latest()
            ->paginate($perPage)
            ->through(fn ($trip) => [
                'id'           => $trip->id,
                'title'        => $trip->title,
                'description'  => $trip->description,
                'image'        => $trip->image,
                'is_public'    => $trip->is_public,
                'favs'         => $trip->favs,

                // Champs dérivés
                'start_date'   => $trip->start_date,   // accessor
                'end_date'     => $trip->end_date,     // accessor
                'total_nights' => $trip->total_nights, // accessor
                'days_count'   => $trip->days_count,
                'steps_count'  => $trip->steps_count,  // withCount
            ]);

        $count = Trip::where('user_id', $user->id)->count();
        $max   = $user->tripLimit();

        return \Inertia\Inertia::render('Trips/Index', [
            'trips' => $trips,
            'limits' => [
                'max'   => $max,
                'count' => $count,
            ],
            'can' => [
                'create_trip' => $count < $max,
            ],
        ]);
    }


    public function create(): InertiaResponse
    {
        $response = Gate::inspect('create', Trip::class);

        if (! $response->allowed()) {
            return Inertia::render('Trips/Index', [
                'trips' => Trip::where('user_id', auth()->id())
                    ->select(['id','title','description','image']) // ⚡
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
        $lock = Cache::lock("user:{$user->id}:create-trip", 5);

        return $lock->block(5, function () use ($request, $user) {
            $this->authorize('create', Trip::class);

            $validated = $request->validated();

            // Dates
            $startDate = $validated['start_date'] ?? null;
            $endDate   = $validated['end_date'] ?? null;
            $nights    = $validated['nights'] ?? null;

            // ⚡ Cohérence des dates / nuits
            if ($startDate && $endDate) {
                $nights = \Carbon\Carbon::parse($startDate)
                    ->diffInDays(\Carbon\Carbon::parse($endDate));
            } elseif ($startDate && $nights !== null) {
                $endDate = \Carbon\Carbon::parse($startDate)
                    ->addDays((int) $nights)
                    ->toDateString();
            } elseif ($startDate && !$nights && !$endDate) {
                $endDate = $startDate;
                $nights  = 0;
            }

            // 1. Créer le Trip
            $trip = Trip::create([
                'user_id'     => $user->id,
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image'       => null, // TODO: upload plus tard
                'budget'      => $validated['budget'] ?? null,
                'currency'    => $validated['currency'] ?? 'EUR',
                'is_public'   => (bool) ($validated['is_public'] ?? false),
            ]);

            // 2. Créer la Step départ
            if ($start = session('start')) {
                $trip->steps()->create([
                    'title'          => "Départ",
                    'location'       => $start,
                    'order'          => 1,
                    'is_destination' => false,
                    'start_date'     => $startDate,
                    'end_date'       => $startDate,
                    'nights'         => 0,
                ]);
            }

            // 3. Créer la Step destination
            if ($destination = session('destination')) {
                $trip->steps()->create([
                    'title'          => $validated['title'] ?? $destination,
                    'location'       => $destination,
                    'order'          => 2,
                    'is_destination' => true,
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                    'nights'         => $nights,
                    'transport_mode' => $validated['transport_mode'] ?? 'car',
                ]);
            }

            return redirect()
                ->route('trips.show', $trip)
                ->with('success', __('Voyage créé !'));
        });
    }


    public function show(Trip $trip): InertiaResponse
    {
        $this->authorize('view', $trip);

        // Compteur de likes
        $trip->loadCount(['favoredBy as favs', 'steps']);

        // Likers (seulement pour le propriétaire)
        $likers = null;
        if (auth()->check() && auth()->user()->can('viewLikers', $trip)) {
            $likers = $trip->favoredBy()
                ->select('users.id','users.name','users.email')
                ->withPivot('created_at')
                ->orderByDesc('favorites.created_at')
                ->paginate(12)
                ->withQueryString();
        }

        // Étapes + relations utiles
        $trip->load([
            'steps' => function ($q) {
                $q->orderBy('order')
                    ->select('id','trip_id','order','title','location',
                        'start_date','end_date','latitude','longitude','nights');
            },
            'steps.activities' => function ($q) {
                $q->orderBy('start_at')
                    ->select('id','step_id','title','description',
                        'start_at','end_at','external_link','cost','currency','category');
            },
            'steps.accommodations' => function ($q) {
                $q->select('id','step_id','title','location','start_date','end_date');
            },
            'checklistItems' => fn($q) => $q->orderBy('order')->orderBy('id'),
        ]);

        // ⚡ Formatage / DTO inline
        $tripData = [
            'id'           => $trip->id,
            'title'        => $trip->title,
            'description'  => $trip->description,
            'image'        => $trip->image,
            'is_public'    => $trip->is_public,
            'favs'         => $trip->favs,

            // Champs dérivés
            'start_date'   => $trip->start_date,   // accessor → min des étapes
            'end_date'     => $trip->end_date,     // accessor → max des étapes
            'total_nights' => $trip->total_nights, // accessor → somme des nuits
            'days_count'   => $trip->days_count,
            'steps_count'  => $trip->steps_count,  // withCount

            // Relations
            'steps' => $trip->steps,
            'checklist_items'    => $trip->checklistItems,
        ];

        return Inertia::render('Trips/Show', [
            'trip'   => $tripData,
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

    public function details(Request $request): InertiaResponse
    {
        $this->authorize('create', Trip::class);

        // On récupère depuis la session les infos précédentes
        $destination = session('destination');
        $start       = session('start');

        if (!$destination) {
            // sécurité : si l’utilisateur saute une étape, on le redirige
            return redirect()->route('trips.create')
                ->with('error', 'Veuillez d’abord choisir une destination.');
        }

        return Inertia::render('Trips/Details', [
            'destination' => $destination,
            'start'       => $start,
        ]);
    }


}
