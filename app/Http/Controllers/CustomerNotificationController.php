<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerNotificationResource;
use App\Models\Customer;
use App\Models\CustomerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerNotificationController extends Controller
{
    /**
     * GET /api/notifications — inbox notifikasi customer yang login.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $notifications = CustomerNotification::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully.',
            'unread_count' => $unreadCount,
            'count' => $notifications->count(),
            'data' => CustomerNotificationResource::collection($notifications),
        ], 200);
    }

    /**
     * POST /api/notifications/{id}/read — tandai satu notifikasi sudah dibaca.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $notification = CustomerNotification::query()
            ->where('customer_id', $customer->id)
            ->find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => new CustomerNotificationResource($notification->fresh()),
        ], 200);
    }
}
