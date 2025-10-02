<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();
        $price = config('services.stripe.price_monthly');

        if ($user->subscribed('default')) {
            return redirect()->route('profile.show')
                ->with('status', __('billing.already_subscribed'));
        }

        return $user->newSubscription('default', $price)->checkout([
            'success_url' => route('profile.show', ['checkout' => 1]),
            'cancel_url'  => route('profile.show', ['checkout' => 0]),
        ]);
    }

    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('profile.show'));
    }

    public function cancel(Request $request)
    {
        $sub = $request->user()->subscription('default');

        if ($sub && $sub->valid()) {
            $sub->cancel();
        }

        return redirect()->route('profile.show')->with('status', __('billing.cancelled'));
    }
}
