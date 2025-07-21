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
    ];

    protected $casts = [
        'is_destination' => 'boolean',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
