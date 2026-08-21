<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:notifications.view')->only(['index', 'count', 'show']);
        $this->middleware('permission:notifications.manage')->only(['markRead', 'markAllRead', 'dismiss']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = AdminNotification::forUser($user)->whereNull('dismissed_at')->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->string('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $notifications->items(),
                'meta' => [
                    'total' => $notifications->total(),
                    'unread' => AdminNotification::forUser($user)->whereNull('read_at')->whereNull('dismissed_at')->count(),
                ],
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function count(Request $request)
    {
        $user = $request->user();
        $count = AdminNotification::forUser($user)->whereNull('read_at')->whereNull('dismissed_at')->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, AdminNotification $notification, AdminNotificationService $service)
    {
        // Authorization: notification must be visible to user
        $user = $request->user();
        $visible = AdminNotification::forUser($user)->whereKey($notification->id)->exists();
        abort_unless($visible, 403);

        $service->markRead($notification);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai dibaca.');
    }

    public function markAllRead(Request $request, AdminNotificationService $service)
    {
        $service->markAllReadForUser($request->user());

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai dibaca.');
    }

    public function dismiss(Request $request, AdminNotification $notification)
    {
        $user = $request->user();
        $visible = AdminNotification::forUser($user)->whereKey($notification->id)->exists();
        abort_unless($visible, 403);

        $notification->update(['dismissed_at' => now(), 'read_at' => $notification->read_at ?? now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi dihapus.');
    }

    public function show(Request $request, AdminNotification $notification)
    {
        $user = $request->user();
        $visible = AdminNotification::forUser($user)->whereKey($notification->id)->exists();
        abort_unless($visible, 404);

        if ($request->wantsJson()) {
            return response()->json($notification);
        }

        return view('notifications.show', compact('notification'));
    }
}
