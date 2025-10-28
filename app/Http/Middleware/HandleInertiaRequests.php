<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Services\TranslationService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
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
                        'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                        'profile_photo_url' => $user->profile_photo_url,
                        'profile_photo_path' => $user->profile_photo_path,

                        'is_premium' => $user->isPremium(),
                        'trip_limit' => $user->tripLimit(),
                        'premium_ends_at' => optional($user->premiumEndsAt())->toIso8601String(),
                        'invoices' => $invoices,
                        'on_grace_period' => $user->isOnGracePeriod(),
                    ];
                },
            ],

            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
                'info'    => fn() => $request->session()->get('info'),
                'status'  => fn() => $request->session()->get('status'),
            ],

            // ✅ Partage des erreurs de validation (après redirect 302)
            'errors' => function () use ($request) {
                $errors = $request->session()->get('errors');
                return $errors ? $errors->getBag('default')->getMessages() : (object) [];
            },

            'csrf_token' => csrf_token(),

            // Dispo dans $page.props.destination / $page.props.start
            'destination' => fn () => $request->session()->get('destination'),
            'start'       => fn () => $request->session()->get('start'),

            // langue & traductions front
            'locale'       => fn () => app()->getLocale(),
            'translations' => fn () => TranslationService::getVueTranslations(app()->getLocale()),
        ]);
    }
}
