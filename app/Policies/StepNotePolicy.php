<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StepNote;

class StepNotePolicy
{
    /**
     * L'utilisateur peut modifier une note s'il en est le propriétaire
     */
    public function update(User $user, StepNote $note): bool
    {
        return $note->user_id === $user->id;
    }

    /**
     * L'utilisateur peut supprimer une note s'il en est le propriétaire
     */
    public function delete(User $user, StepNote $note): bool
    {
        return $note->user_id === $user->id;
    }

    /**
     * L'utilisateur peut créer une note s'il est connecté
     */
    public function create(User $user): bool
    {
        return $user !== null;
    }
}
