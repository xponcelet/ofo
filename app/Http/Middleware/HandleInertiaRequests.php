<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => function () use ($request) {
                    $u = $request->user();
                    if (!$u) return null;

                    /** @var \App\Models\User $user */
                    $user = $u->fresh();
                    $user->loadMissing('subscriptions');

                    $invoices = [];
                    if ($request->routeIs('profile.show') && $user->hasStripeId()) {
                        try {
                            $invoices = $user->invoices()->map(function ($inv) {
                                $date = $inv->date();
                                $stripe = $inv->asStripeInvoice();
                                return [
                                    'id' => $inv->id,
                                    'number' => $inv->number,
                                    'date' => $date ? $date->toIso8601String() : null,
                                    'total' => $inv->total(),
                                    'hosted_invoice_url' => $stripe->hosted_invoice_url ?? null,
                                    'pdf' => $stripe->invoice_pdf ?? null,
                                ];
                            })->all();
                        } catch (\Throwable $e) {
                            $invoices = [];
                        }
                    }

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        // ✅ AJOUTS pour la photo & compat Jetstream
                        'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                        'profile_photo_url' => $user->profile_photo_url,   // <- important
                        'profile_photo_path' => $user->profile_photo_path, // optionnel

                        // Ton état d’abonnement
                        'is_premium' => $user->isPremium(),
                        'trip_limit' => $user->tripLimit(),
                        'premium_ends_at' => optional($user->premiumEndsAt())->toIso8601String(),
                        'invoices' => $invoices,
                        'on_grace_period' => $user->isOnGracePeriod(),
                    ];
                },
            ],

            'flash' => [
                'status' => fn() => $request->session()->get('status'),
            ],
            'csrf_token' => fn() => csrf_token(),
        ]);
    }
}
