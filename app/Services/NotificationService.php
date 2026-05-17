<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public function createNotification(User $user, string $type, string $title, string $body): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'is_read' => false,
        ]);
    }

    /**
     * Get paginated notifications for a user.
     */
    public function getNotifications(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->notifications()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->update(['is_read' => true]);

        return $notification->refresh();
    }

    /**
     * Get the count of unread notifications for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()
            ->where('is_read', false)
            ->count();
    }
}
