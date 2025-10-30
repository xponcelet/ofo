<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripUser;
use App\Models\ChecklistItem;
use App\Models\Step;
use App\Models\Activity;
use App\Models\Accommodation;
use App\Models\StepNote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    /**
     * Liste des voyages de l’utilisateur
     */
    public function index(Request $request): InertiaResponse
    {
        if (!Auth::check()) {
            return Inertia::render('Trips/GuestPlaceholder');
        }

        $user = Auth::user();
        $perPage = (int) $request->integer('per_page', 12);
        $search = $request->input('search');

        $trips = Trip::query()
            ->whereHas('users', fn($q) => $q->where('user_id', $user->id))
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhereHas('steps', function ($sq) use ($search) {
                            $sq->where('country', 'like', "%{$search}%");
                        });
                });
            })
            ->with([
                'steps:id,trip_id,title,latitude,longitude,country,country_code,is_destination,start_date,end_date',
                'users'
            ])
            ->withCount(['steps', 'favoredBy as favs'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($trip) use ($user) {
                $pivot = $trip->users()->where('user_id', $user->id)->first()?->pivot;

                // Étape de destination
                $destination = $trip->steps->firstWhere('is_destination', true);

                // Détermination du statut
                $departure = $pivot?->departure_date ? now()->diffInDays($pivot->departure_date, false) : null;
                if (!$pivot?->departure_date) {
                    $status = 'sans_date';
                } elseif ($departure > 0) {
                    $status = 'a_venir';
                } elseif ($departure <= 0 && $trip->steps->max('end_date') >= now()->toDateString()) {
                    $status = 'en_cours';
                } else {
                    $status = 'termine';
                }

                return [
                    'id' => $trip->id,
                    'role' => $pivot?->role,
                    'trip_user_id' => $pivot?->id ?? null,
                    'title' => $trip->title,
                    'description' => $trip->description,
                    'image' => $trip->image,
                    'is_public' => $trip->is_public,
                    'favs' => $trip->favs,
                    'departure_date' => $pivot?->departure_date,
                    'steps_count' => $trip->steps_count,
                    'latitude' => $destination?->latitude,
                    'longitude' => $destination?->longitude,
                    'destination_country' => $destination?->country,
                    'destination_country_code' => $destination?->country_code,
                    'status' => $status,
                ];
            });

        return Inertia::render('Trips/Index', [
            'trips' => $trips,
            'filters' => ['search' => $search],
            'limits' => [
                'count' => $user->trips()->count(),
                'max' => $user->tripLimit(),
            ],
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create(): InertiaResponse
    {
        $user  = auth()->user();
        $count = $user->trips()->count();
        $max   = $user->tripLimit();

        if ($count >= $max) {
            return Inertia::render('Trips/Index', [
                'trips' => Trip::where('user_id', $user->id)
                    ->select(['id','title','description','image'])
                    ->withCount(['steps','favoredBy as favs'])
                    ->latest()->paginate(12),
                'limits' => [
                    'max'   => $max,
                    'count' => $count,
                ],
                'errors' => [
                    'base' => __('Vous avez atteint la limite de :max voyages.', ['max' => $max]),
                ],
            ]);
        }

        $response = Gate::inspect('create', Trip::class);
        if (! $response->allowed()) {
            abort(403);
        }

        return Inertia::render('Trips/Create');
    }

    /**
     * Enregistrement du voyage
     */
    public function store(StoreTripRequest $request)
    {
        $user = auth()->user();

        if ($user->trips()->count() >= $user->tripLimit()) {
            return redirect()
                ->route('trips.index')
                ->with('error', __("Vous avez atteint la limite de {$user->tripLimit()} voyages. Passez Premium pour en créer plus !"));
        }

        $lock = Cache::lock("user:{$user->id}:create-trip", 5);

        return $lock->block(5, function () use ($request, $user) {
            $this->authorize('create', Trip::class);

            $validated = $request->validated();

            $startDate = $validated['start_date'] ?? null;
            $endDate   = $validated['end_date'] ?? null;
            $nights    = $validated['nights'] ?? null;

            if ($startDate && $endDate) {
                $nights = Carbon::parse($startDate)
                    ->diffInDays(Carbon::parse($endDate));
            } elseif ($startDate && $nights !== null) {
                $endDate = Carbon::parse($startDate)
                    ->addDays((int) $nights)
                    ->toDateString();
            } elseif ($startDate && !$nights && !$endDate) {
                $endDate = $startDate;
                $nights  = 0;
            }

            $trip = Trip::create([
                'user_id'     => $user->id,
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image'       => null,
                'budget'      => $validated['budget'] ?? null,
                'currency'    => $validated['currency'] ?? 'EUR',
                'is_public'   => (bool) ($validated['is_public'] ?? false),
            ]);

            if ($start = session('start')) {
                $trip->steps()->create([
                    'title'          => __('trip.departure'),
                    'location'       => $start,
                    'order'          => 1,
                    'is_destination' => false,
                    'start_date'     => $startDate,
                    'end_date'       => $startDate,
                    'nights'         => 0,
                ]);
            }

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
                ->with('success', __('trip.created'));
        });
    }

    /**
     * Affichage d’un voyage
     */
    public function show(Trip $trip): InertiaResponse
    {
        $this->authorize('view', $trip);

        $trip->loadCount(['favoredBy as favs', 'steps']);

        $likers = null;
        if (auth()->check() && auth()->user()->can('viewLikers', $trip)) {
            $likers = $trip->favoredBy()
                ->select('users.id', 'users.name', 'users.email')
                ->withPivot('created_at')
                ->orderByDesc('favorites.created_at')
                ->paginate(12)
                ->withQueryString();
        }

        $trip->load([
            'steps' => function ($q) {
                $q->orderBy('order')
                    ->with([
                        'note:id,step_id,content,user_id',
                        'activities' => fn($a) => $a
                            ->orderBy('start_at')
                            ->select(
                                'id', 'step_id', 'title', 'description', 'location',
                                'start_at', 'end_at', 'external_link', 'cost',
                                'currency', 'category', 'latitude', 'longitude'
                            ),
                        'accommodations:id,step_id,title,location,start_date,end_date'
                    ])
                    ->select(
                        'id', 'trip_id', 'order', 'title', 'description', 'location',
                        'country_code', 'country', 'start_date', 'end_date',
                        'latitude', 'longitude', 'nights', 'distance_to_next', 'duration_to_next'
                    );
            },
            'checklistItems' => fn($q) => $q->orderBy('order')->orderBy('id'),
        ]);

        $days = [];
        if ($trip->start_date && $trip->end_date) {
            $period = new DatePeriod(
                new \DateTime($trip->start_date),
                new DateInterval('P1D'),
                (new \DateTime($trip->end_date))->modify('+1 day')
            );

            foreach ($period as $date) {
                $dayStep = $trip->steps->first(function ($step) use ($date) {
                    return $date >= new \DateTime($step->start_date)
                        && $date <= new \DateTime($step->end_date);
                });

                $days[] = [
                    'date'     => $date->format('Y-m-d'),
                    'location' => $dayStep->location ?? null,
                    'step_id'  => $dayStep->id ?? null,
                    'step'     => $dayStep ?? null,
                ];
            }
        }

        $activities = $trip->steps->flatMap(fn($step) =>
        $step->activities->map(fn($a) => [
            'id' => $a->id,
            'step_id' => $a->step_id,
            'title' => $a->title,
            'description' => $a->description,
            'location' => $a->location,
            'start_at' => optional($a->start_at)->toDateTimeString(),
            'end_at' => optional($a->end_at)->toDateTimeString(),
            'external_link' => $a->external_link,
            'cost' => $a->cost,
            'currency' => $a->currency,
            'category' => $a->category,
            'latitude' => $a->latitude,
            'longitude' => $a->longitude,
            'date' => optional($a->start_at)->toDateString(),
            'step_location' => $step->location,
            'step_title' => $step->title,
        ])
        )->values();

        $tripUser = TripUser::where('trip_id', $trip->id)
            ->where('user_id', auth()->id())
            ->first();

        $states = $tripUser
            ? \App\Models\TripUserChecklistItem::where('trip_user_id', $tripUser->id)
                ->pluck('is_checked', 'checklist_item_id')
            : collect();

        return Inertia::render('Trips/Show', [
            'trip' => [
                'id' => $trip->id,
                'title' => $trip->title,
                'description' => $trip->description,
                'image' => $trip->image,
                'is_public' => $trip->is_public,
                'favs' => $trip->favs,
                'start_date' => $trip->start_date,
                'end_date' => $trip->end_date,
                'steps_count' => $trip->steps_count,
                'steps' => $trip->steps,
                'checklist_items'=> $trip->checklistItems,
                'days' => $days,
            ],
            'steps'      => $trip->steps,
            'activities' => $activities,
            'favs'       => $trip->favs,
            'likers'     => $likers,
            'states'     => $states,
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
            ->with('success', __('trip.updated'));
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);
        $trip->delete();

        return redirect()->route('trips.index')->with('success', __('trip.deleted'));
    }

    public function details(Request $request): InertiaResponse
    {
        $this->authorize('create', Trip::class);

        $destination = session('destination');
        $start       = session('start');

        if (!$destination) {
            return redirect()->route('trips.create')
                ->with('error', __('trip.choose_destination_first'));
        }

        return Inertia::render('Trips/Details', [
            'destination' => $destination,
            'start'       => $start,
        ]);
    }

    public function duplicate(Trip $trip)
    {
        $this->authorize('view', $trip);

        $user = auth()->user();
        $newTrip = null;

        DB::transaction(function () use ($trip, $user, &$newTrip) {
            $newTrip = $trip->replicate();
            $newTrip->user_id = $user->id;
            $newTrip->title = $trip->title . ' (copie)';
            $newTrip->save();

            TripUser::create([
                'trip_id' => $newTrip->id,
                'user_id' => $user->id,
                'role'    => 'owner',
            ]);

            foreach ($trip->checklistItems as $item) {
                $newItem = $item->replicate();
                $newItem->trip_id = $newTrip->id;
                $newItem->save();
            }

            $stepMap = [];
            foreach ($trip->steps as $step) {
                $newStep = $step->replicate();
                $newStep->trip_id = $newTrip->id;
                $newStep->save();
                $stepMap[$step->id] = $newStep->id;
            }

            $activities = Activity::whereIn('step_id', $trip->steps->pluck('id'))->get();
            foreach ($activities as $activity) {
                $newActivity = $activity->replicate();
                if (isset($stepMap[$activity->step_id])) {
                    $newActivity->step_id = $stepMap[$activity->step_id];
                }
                $newActivity->save();
            }

            $stepNotes = StepNote::whereIn('step_id', $trip->steps->pluck('id'))->get();
            foreach ($stepNotes as $note) {
                $newNote = $note->replicate();
                if (isset($stepMap[$note->step_id])) {
                    $newNote->step_id = $stepMap[$note->step_id];
                }
                $newNote->user_id = $user->id;
                $newNote->save();
            }
        });

        return redirect()
            ->route('trips.show', $newTrip)
            ->with('success', 'Voyage dupliqué avec succès.');
    }
}
