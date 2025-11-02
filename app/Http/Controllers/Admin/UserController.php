<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn($user) => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'is_blocked' => $user->is_blocked,
                'created_at' => $user->created_at,
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function toggleBlock(User $user): RedirectResponse
    {
        //  Protection : on ne peut pas se bloquer soi-même
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Vous ne pouvez pas bloquer votre propre compte.');
        }

        $user->update(['is_blocked' => ! $user->is_blocked]);

        return back()->with(
            'success',
            $user->is_blocked
                ? "L'utilisateur {$user->name} a été bloqué."
                : "L'utilisateur {$user->name} a été débloqué."
        );
    }

    public function destroy(User $user): RedirectResponse
    {
        //  Protection : on ne peut pas se supprimer soi-même
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return back()->with('success', "L'utilisateur {$user->name} a été supprimé.");
    }
}
