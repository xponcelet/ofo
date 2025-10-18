<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    /** @use HasFactory<\Database\Factories\StepFactory> */
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'order',
        'is_destination',
        'country',
        'country_code',
        'transport_mode',
        'distance_to_next',
        'duration_to_next',
        'nights',
    ];

    protected $casts = [
        'is_destination' => 'boolean',
        'latitude' => 'float',   // pour avoir le bon format avec Google maps et MapBox
        'longitude' => 'float',  // pour avoir le bon format avec Google maps et MapBox
        'distance_to_next' => 'float',
        'duration_to_next' => 'float',
        'nights'     => 'integer',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function note()
    {
        return $this->hasOne(StepNote::class)->where('user_id', auth()->id());
    }


}
