<?php

namespace App\Http\Middleware;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Session;

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
        $user = $request->user();

        $cartData = ['itemCount' => 0, 'subtotal' => 0];
        $notifications = [];

        if ($user && $user->role === 'customer') {
            $cart = \App\Models\Cart::where('user_id', $user->id)->with('items')->first();
            if ($cart) {
                $cartData = [
                    'itemCount' => $cart->items->count(),
                    'subtotal' => $cart->items->sum('total_price'),
                ];
            }
        }

        if ($user) {
            $notifications = \App\Models\Notification::where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get(['id', 'title', 'body', 'link', 'is_read', 'created_at'])
                ->toArray();
        }

        return [
            ...parent::share($request),
            'flash' => [
                'success' => Session::get('success'),
                'error' => Session::get('error'),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
            ],
            'cart' => $cartData,
            'notifications' => $notifications,
            'notificationsCount' => $user
                ? app(NotificationService::class)->getUnreadCount($user)
                : 0,
        ];
    }
}
