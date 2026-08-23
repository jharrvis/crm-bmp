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
        $this->middleware('permission:notifications.manage')->only(['markRead', 'markAllRead', 'dismiss', 'resolve', 'snooze']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = AdminNotification::forUser($user)->whereNull('dismissed_at')->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity'));
        }
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->string('source_type'));
        }
        if ($request->string('filter') === 'unread') {
            $query->whereNull('read_at')->whereNull('resolved_at')->where(function ($q) {
                $q->whereNull('snoozed_until')->orWhere('snoozed_until', '<=', now());
            });
        }
        if ($request->string('filter') === 'action_required') {
            $query->where('action_required', true)->whereNull('resolved_at')->whereNull('dismissed_at')->where(function ($q) {
                $q->whereNull('snoozed_until')->orWhere('snoozed_until', '<=', now());
            });
        }
        if ($request->string('filter') === 'unresolved') {
            $query->whereNull('resolved_at');
        }

        $notifications = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            // Enrich with resolved CTA server-side — jangan percaya URL dari payload
            $items = collect($notifications->items())->map(function (AdminNotification $n) use ($user) {
                $action = null;
                if ($n->action_key) {
                    $action = \App\Services\Admin\NotificationTypeRegistry::resolveAction($n->action_key, $n->payload ?? [], $user);
                }
                $arr = $n->toArray();
                $arr['resolved_action'] = $action; // null jika permission tidak ada atau source invalid
                return $arr;
            });
            return response()->json([
                'data' => $items,
                'meta' => [
                    'total' => $notifications->total(),
                    'unread' => app(\App\Services\Admin\AdminNotificationService::class)->unreadCountForUser($user),
                    'action_required' => app(\App\Services\Admin\AdminNotificationService::class)->actionRequiredCountForUser($user),
                ],
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function count(Request $request)
    {
        $user = $request->user();
        $service = app(\App\Services\Admin\AdminNotificationService::class);

        return response()->json([
            'count' => $service->unreadCountForUser($user),
            'action_required' => $service->actionRequiredCountForUser($user),
        ]);
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

    public function resolve(Request $request, AdminNotification $notification, AdminNotificationService $service)
    {
        $user = $request->user();
        $visible = AdminNotification::forUser($user)->whereKey($notification->id)->exists();
        abort_unless($visible, 403);

        $service->markResolved($notification, $user);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai selesai.');
    }

    public function snooze(Request $request, AdminNotification $notification, AdminNotificationService $service)
    {
        $request->validate(['hours' => 'nullable|integer|min:1|max:168']);
        $user = $request->user();
        $visible = AdminNotification::forUser($user)->whereKey($notification->id)->exists();
        abort_unless($visible, 403);

        $service->snooze($notification, (int) ($request->input('hours', 24)));

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditunda.');
    }

    public function show(Request $request, AdminNotification $notification)
    {
        $user = $request->user();
        $visible = AdminNotification::forUser($user)->whereKey($notification->id)->exists();
        abort_unless($visible, 404);

        $resolvedAction = null;
        if ($notification->action_key) {
            $resolvedAction = \App\Services\Admin\NotificationTypeRegistry::resolveAction($notification->action_key, $notification->payload ?? [], $user);
        }

        if ($request->wantsJson()) {
            $data = $notification->toArray();
            $data['resolved_action'] = $resolvedAction;
            return response()->json($data);
        }

        return view('notifications.show', compact('notification', 'resolvedAction'));
    }
}
