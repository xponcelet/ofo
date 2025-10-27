<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class Trip extends Model
{
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
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_public'  => 'boolean',
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
    ];

    /* -----------------------------
     | SCOPES
     ----------------------------- */
    public function scopeIsPublic($query)
    {
        return $query->where('is_public', true);
    }

    /* -----------------------------
     | RELATIONS
     ----------------------------- */

    // 👥 Participants (relation pivot trip_user)
    public function users()
    {
        return $this->belongsToMany(User::class, 'trip_users')
            ->withPivot([
                'start_location',
                'latitude',
                'longitude',
                'departure_date',
                'role',
            ])
            ->withTimestamps();
    }

    // 🚩 Étapes du voyage
    public function steps()
    {
        return $this->hasMany(Step::class)->orderBy('order');
    }

    // 🏨 Hébergements liés
    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    // 🎯 Activités (via étapes)
    public function activities()
    {
        return $this->hasManyThrough(Activity::class, Step::class);
    }

    // ✅ Checklist commune au voyage
    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('order');
    }

    // 💬 Notes via étapes
    public function stepNotes()
    {
        return $this->hasManyThrough(StepNote::class, Step::class);
    }

    // ⭐ Gestion des favoris
    public function favoredBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function isFavoredBy(User $user): bool
    {
        return $this->favoredBy()->where('user_id', $user->id)->exists();
    }

    /* -----------------------------
     | ATTRIBUTS DÉRIVÉS
     ----------------------------- */

    // Dates calculées depuis les étapes
    public function getStartDateAttribute(): ?string
    {
        return $this->steps()->min('start_date');
    }

    public function getEndDateAttribute(): ?string
    {
        return $this->steps()->max('end_date');
    }

    public function getTotalNightsAttribute(): int
    {
        if ($this->start_date && $this->end_date) {
            return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date));
        }
        return 0;
    }

    public function getDaysCountAttribute(): int
    {
        if ($this->start_date && $this->end_date) {
            return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;
        }
        return 0;
    }

    /* -----------------------------
     | ÉVÉNEMENTS / CALLBACKS
     ----------------------------- */

    protected static function booted(): void
    {
        // À la création : préremplir la checklist si config présente
        static::created(function (Trip $trip) {
            $defaults = Config::get('checklist.defaults', []);
            if (empty($defaults)) {
                return;
            }

            $trip->checklistItems()->createMany(
                collect($defaults)->values()->map(fn ($label, $i) => [
                    'label' => $label,
                    'order' => $i,
                ])->all()
            );
        });
    }
}
