<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'label',
        'is_checked',
        'order',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
    public function userStates()
    {
        return $this->hasMany(\App\Models\TripUserChecklistItem::class);
    }

}
