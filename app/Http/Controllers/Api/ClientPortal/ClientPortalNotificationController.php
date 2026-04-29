<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\ClientPortalAccount;
use App\Models\ClientPortalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientPortalNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        $notifications = ClientPortalNotification::query()
            ->where('client_id', $account->client_id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $notifications->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'payload' => $notification->payload,
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function markRead(Request $request, ClientPortalNotification $notification): JsonResponse
    {
        $notification = $this->authorizedNotification($request, $notification);

        $notification->forceFill([
            'read_at' => $notification->read_at ?? now(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca.',
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        ClientPortalNotification::query()
            ->where('client_id', $account->client_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai sebagai dibaca.',
        ]);
    }

    private function authorizedNotification(Request $request, ClientPortalNotification $notification): ClientPortalNotification
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        abort_unless($notification->client_id === $account->client_id, 404);

        return $notification;
    }
}
