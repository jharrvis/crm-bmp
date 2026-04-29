<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\ClientPortalAccount;
use App\Models\ClientPortalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientPortalDashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();
        $client = $account->client()->with(['subscriptions.package'])->firstOrFail();

        $subscriptions = $client->subscriptions;
        $invoicesQuery = $client->invoices();

        return response()->json([
            'client' => [
                'id' => $client->id,
                'client_code' => $client->client_code,
                'name' => $client->name,
                'status' => $client->status,
            ],
            'summary' => [
                'active_subscriptions_count' => $subscriptions->where('status', 'active')->count(),
                'total_subscriptions_count' => $subscriptions->count(),
                'unpaid_invoices_count' => (clone $invoicesQuery)->where('status', 'unpaid')->count(),
                'overdue_invoices_count' => (clone $invoicesQuery)->where('status', 'overdue')->count(),
                'unread_notifications_count' => ClientPortalNotification::query()
                    ->where('client_id', $client->id)
                    ->whereNull('read_at')
                    ->count(),
            ],
            'recent_invoices' => (clone $invoicesQuery)
                ->latest('invoice_date')
                ->limit(5)
                ->get()
                ->map(fn ($invoice) => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date?->toDateString(),
                    'due_date' => $invoice->due_date?->toDateString(),
                    'status' => $invoice->status,
                    'total_amount' => (float) $invoice->total_amount,
                ]),
            'recent_notifications' => ClientPortalNotification::query()
                ->where('client_id', $client->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->toIso8601String(),
                ]),
        ]);
    }
}
