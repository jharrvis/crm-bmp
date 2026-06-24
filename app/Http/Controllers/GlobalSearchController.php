<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Global search across key entities.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => [], 'total' => 0]);
        }

        $results = [];
        $total   = 0;

        // --- Clients ---
        $clients = Client::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('client_code', 'like', "%{$query}%")
                  ->orWhere('address', 'like', "%{$query}%")
                  ->orWhere('city', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'client_code', 'city', 'status']);

        if ($clients->isNotEmpty()) {
            $results[] = [
                'group' => 'Pelanggan',
                'icon'  => 'users',
                'items' => $clients->map(fn($c) => [
                    'id'       => $c->id,
                    'title'    => $c->name,
                    'subtitle' => implode(' · ', array_filter([$c->client_code, $c->city])),
                    'badge'    => $c->status,
                    'url'      => route('clients.show', $c->id),
                ])->values(),
            ];
            $total += $clients->count();
        }

        // --- Invoices ---
        $invoices = Invoice::query()
            ->where('invoice_number', 'like', "%{$query}%")
            ->with('client:id,name')
            ->limit(5)
            ->get(['id', 'client_id', 'invoice_number', 'status', 'total_amount']);

        if ($invoices->isNotEmpty()) {
            $results[] = [
                'group' => 'Invoice',
                'icon'  => 'file-text',
                'items' => $invoices->map(fn($i) => [
                    'id'       => $i->id,
                    'title'    => $i->invoice_number,
                    'subtitle' => implode(' · ', array_filter([
                        $i->client?->name,
                        'Rp ' . number_format((float) $i->total_amount, 0, ',', '.'),
                    ])),
                    'badge'    => $i->status,
                    'url'      => route('invoices.show', $i->id),
                ])->values(),
            ];
            $total += $invoices->count();
        }

        // --- Tickets ---
        $tickets = Ticket::query()
            ->where(function ($q) use ($query) {
                $q->where('ticket_number', 'like', "%{$query}%")
                  ->orWhere('subject', 'like', "%{$query}%");
            })
            ->with('client:id,name')
            ->limit(5)
            ->get(['id', 'client_id', 'ticket_number', 'subject', 'status', 'priority']);

        if ($tickets->isNotEmpty()) {
            $results[] = [
                'group' => 'Tiket',
                'icon'  => 'ticket',
                'items' => $tickets->map(fn($t) => [
                    'id'       => $t->id,
                    'title'    => $t->ticket_number,
                    'subtitle' => $t->subject,
                    'badge'    => $t->status,
                    'url'      => route('tickets.show', $t->id),
                ])->values(),
            ];
            $total += $tickets->count();
        }

        // --- Subscriptions ---
        $subscriptions = Subscription::query()
            ->where('subscription_code', 'like', "%{$query}%")
            ->with(['client:id,name', 'package:id,name'])
            ->limit(5)
            ->get(['id', 'client_id', 'package_id', 'subscription_code', 'status']);

        if ($subscriptions->isNotEmpty()) {
            $results[] = [
                'group' => 'Langganan',
                'icon'  => 'wifi',
                'items' => $subscriptions->map(fn($s) => [
                    'id'       => $s->id,
                    'title'    => $s->subscription_code,
                    'subtitle' => implode(' · ', array_filter([
                        $s->client?->name,
                        $s->package?->name,
                    ])),
                    'badge'    => $s->status,
                    'url'      => route('subscriptions.show', $s->id),
                ])->values(),
            ];
            $total += $subscriptions->count();
        }

        return response()->json([
            'results' => $results,
            'total'   => $total,
            'query'   => $query,
        ]);
    }
}
