<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Trip;
use Laravel\Cashier\Billable;
use Carbon\Carbon;

class User extends Authenticatable // implements MustVerifyEmail --> Pour l'instant
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Billable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'locale',
        'role',
        'is_blocked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
        ];
    }

    public function favoriteTrips()
    {
        return $this->belongsToMany(Trip::class, 'favorites')->withTimestamps();
    }

    public function trips()
    {
        return $this->belongsToMany(Trip::class, 'trip_users')
            ->withPivot(['start_location', 'latitude', 'longitude', 'departure_date'])
            ->withTimestamps();
    }

    public function isPremium(): bool
    {
        $sub = $this->subscription('default');
        return $sub && ($sub->valid() || $sub->onGracePeriod());
    }

    public function isOnGracePeriod(): bool
    {
        $sub = $this->subscription('default');
        return $sub ? $sub->onGracePeriod() : false;
    }

    public function premiumEndsAt(): ?Carbon
    {
        $subscription = $this->subscription('default');

        if ($subscription && $subscription->valid()) {
            $stripeSub = $subscription->asStripeSubscription();
            return Carbon::createFromTimestamp($stripeSub->current_period_end);
        }

        return null;
    }


    public function tripLimit(): int
    {
        // Si abonnement pas de limite
        if ($this->isPremium()) {
            return PHP_INT_MAX; // limite virtuelle énorme
        }

        // sinon limite
        return (int) ($this->trip_limit ?? config('trips.default_max_per_user'));
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

}
