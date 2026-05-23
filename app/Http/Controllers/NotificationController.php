<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notifications = $this->notificationService->getNotifications($user);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        $this->notificationService->markAsRead($notification);

        return redirect()->back()
            ->with('success', 'تم تحديد الإشعار كمقروء.');
    }
}
