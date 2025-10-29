<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'user_id',
        'role',
        'start_location',
        'latitude',
        'longitude',
        'departure_date',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checklistStates()
    {
        return $this->hasMany(TripUserChecklistItem::class);
    }
}
