<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notifications for dropdown
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $notifications = $this->notificationService->getRecentNotifications($userId, 15);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        if ($request->ajax()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread count for badge
     */
    public function getUnreadCount()
    {
        $count = $this->notificationService->getUnreadCount(Auth::id());
        return response()->json(['count' => $count]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $success = $this->notificationService->markAsRead($id);
        return response()->json(['success' => $success]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::id());
        return response()->json(['success' => true]);
    }

    /**
     * Get notifications data for AJAX
     */
    public function getNotifications(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->get('limit', 10);
        
        $notifications = $this->notificationService->getRecentNotifications($userId, $limit);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon,
                    'time_ago' => $notification->time_ago,
                    'url' => $notification->url,
                    'is_read' => $notification->is_read,
                    'from_user' => $notification->fromUser ? $notification->fromUser->name : 'System',
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }
}