<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    /** @use HasFactory<\Database\Factories\TripFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'start_date',
        'end_date',
        'budget',
        'currency',
        'average_rating',
        'is_public',
    ];
    public function scopeIsPublic($query)
    {
        return $query->where('is_public', true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function steps()
    {
        return $this->hasMany(Step::class)->orderBy('order');
    }

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    // Gestion des favoris
    public function favoredBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function isFavoredBy(User $user): bool
    {
        return $this->favoredBy()->where('user_id', $user->id)->exists();
    }

    public function activities()
    {
        // via steps
        return $this->hasManyThrough(Activity::class, Step::class);
    }

    /*
            public function transports()
            {
                return $this->hasMany(Transport::class);
            }

            public function activities()
            {
                return $this->hasMany(Activity::class);
            }

            public function collaborators()
            {
                return $this->hasMany(TripUser::class);
            }

            public function comments()
            {
                return $this->morphMany(Comment::class, 'commentable');
            }

            public function favorites()
            {
                return $this->hasMany(FavoriteTrip::class);
            }
        */

}
