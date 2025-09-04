<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Inertia\Inertia;
use App\Http\Requests\PublicTripIndexRequest;

class PublicTripController extends Controller
{
    public function index(PublicTripIndexRequest $request)
    {
        $validated = $request->validated();
        $sort = $validated['sort'] ?? 'latest';

        $trips = Trip::query()
            ->isPublic()
            ->withCount('steps')
            ->when($validated['q'] ?? null, function ($q, $term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                });
            })
            ->when($sort === 'latest', fn($q) => $q->latest())
            ->when($sort === 'oldest', fn($q) => $q->oldest())
            ->when($sort === 'title',  fn($q) => $q->orderBy('title'))
            ->select(['id','title','description','image','start_date','end_date']) // whitelisting propre
            ->paginate($request->perPage())
            ->withQueryString();

        return Inertia::render('Public/Trips/Index', [
            'trips' => $trips,
            'filters' => [
                'q'    => $validated['q'] ?? null,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Trip $trip)
    {
        abort_unless($trip->is_public, 404);

        $trip->load([
            'steps:id,trip_id,title,location,latitude,longitude,order',
        ]);

        $isFavorite = false;

        if (auth()->check()) {
            $isFavorite = $trip->isFavoredBy(auth()->user());
        }

        return Inertia::render('Public/Trips/Show', [
            'trip' => $trip,
            'isFavorite' => $isFavorite,
        ]);
    }
}
