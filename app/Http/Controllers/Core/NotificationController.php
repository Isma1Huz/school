<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Modules\Core\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * REST endpoints for the in-app notification bell.
 * All routes are behind auth + tenant middleware.
 */
final class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * GET /notifications/unread
     * Returns the unread count and up to 20 most recent unread notifications.
     */
    public function unread(Request $request): JsonResponse
    {
        $user          = $request->user();
        $notifications = $this->notificationService->getUnread($user, 20);

        return response()->json([
            'count'         => $notifications->count(),
            'notifications' => $notifications->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'channel'    => $n->channel,
                'created_at' => $n->created_at,
            ]),
        ]);
    }

    /**
     * POST /notifications/{id}/read
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $this->notificationService->markAsRead($id);

        return response()->json(['ok' => true]);
    }

    /**
     * POST /notifications/read-all
     * Mark every unread notification for the current user as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json(['ok' => true]);
    }
}
