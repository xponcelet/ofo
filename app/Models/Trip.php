<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Trip extends Model
{
    /** @use HasFactory<\Database\Factories\TripFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'budget',
        'currency',
        'average_rating',
        'is_public',
    ];
    public function scopeIsPublic($query)
    {
        return $query->where('is_public', true);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'trip_user')
            ->withPivot([
                'start_location',
                'latitude',
                'longitude',
                'departure_date',
            ])
            ->withTimestamps();
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

    /** on déduit ces champs des steps*/
    public function getStartDateAttribute(): ?string
    {
        return $this->steps()->min('start_date');
    }

    public function getEndDateAttribute(): ?string
    {
        return $this->steps()->max('end_date');
    }

    public function getTotalNightsAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date));
        }
        return 0;
    }

    public function getDaysCountAttribute()
    {
        if ($this->start_date && $this->end_date) {
            // +1 pour inclure le jour d'arrivée
            return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;
        }
        return 0;
    }

    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('order');
    }
    /**
     * Préremplit la checklist à la création du voyage
     */

    protected static function booted(): void
    {
        static::created(function (Trip $trip) {
            $defaults = config('checklist.defaults', []); // cf. config/checklist.php
            if (empty($defaults)) return;

            $trip->checklistItems()->createMany(
                collect($defaults)->values()->map(fn ($label, $i) => [
                    'label'      => $label,
                    'is_checked' => false,
                    'order'      => $i,
                ])->all()
            );
        });
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
