<?php
namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function create(User $user, int $tripId): bool
    {
        return \App\Models\Trip::whereKey($tripId)->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Activity $activity): bool
    {
        return $activity->step->trip->user_id === $user->id;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $activity->step->trip->user_id === $user->id;
    }
}
