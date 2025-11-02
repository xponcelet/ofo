<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Inertia\Inertia;

class TripController extends Controller
{
    public function index()
    {
        $trips = Trip::with('user')
            ->latest()
            ->paginate(10)
            ->through(fn($trip) => [
                'id' => $trip->id,
                'title' => $trip->title,
                'user' => $trip->user ? [
                    'id' => $trip->user->id,
                    'name' => $trip->user->name,
                ] : null,
                'budget' => $trip->budget,
                'is_public' => (bool) $trip->is_public,
                'created_at' => $trip->created_at,
            ]);

        return Inertia::render('Admin/Trips/Index', [
            'trips' => $trips,
        ]);
    }
    public function destroy(Trip $trip)
    {
        $trip->delete();
        return back()->with('success', 'Voyage supprimé.');
    }
}
