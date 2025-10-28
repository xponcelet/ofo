<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Inertia\Inertia;

class TripController extends Controller
{
    public function index()
    {
        $trips = Trip::query()
            ->select('id', 'title', 'user_id', 'is_public', 'budget', 'created_at')
            ->with('user:id,name,email')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Trips/Index', [
            'trips' => $trips,
        ]);
    }
}
