<?php

namespace App\Http\Controllers\Api;

use App\Models\Notifications;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    // GET notifications
    public function index()
    {
        $notifications = Notification::where(
            'user_id',
            auth()->id()
        )->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    // READ notification
    public function read($id)
    {
        $notification = Notification::where(
            'user_id',
            auth()->id()
        )->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update([
            'is_read' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }
}