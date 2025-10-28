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

    /**
     * 🔗 Relation : ce lien appartient à un voyage
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * 🔗 Relation : ce lien appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ✅ États individuels de checklist liés à ce trip_user
     */
    public function checklistStates()
    {
        return $this->hasMany(TripUserChecklistItem::class);
    }
}
