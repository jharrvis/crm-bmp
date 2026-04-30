<?php

namespace App\Http\Controllers;

use App\Models\ClientPortalNotification;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::query()
            ->with(['client.primaryContact', 'subscription.package.service', 'assignedUser'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        $tickets = $query->get();

        return view('tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'client.primaryContact',
            'subscription.package.service',
            'assignedUser',
            'replies.portalAccount.client',
            'replies.user',
        ]);

        $staffUsers = User::role(['Owner', 'Admin', 'Employee'])
            ->orderBy('name')
            ->get();

        return view('tickets.show', compact('ticket', 'staffUsers'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $updates = $validated;

        if ($ticket->first_response_at === null && in_array($validated['status'], ['in_progress', 'waiting_client', 'resolved', 'closed'], true)) {
            $updates['first_response_at'] = now();
        }

        $updates['resolved_at'] = $validated['status'] === 'resolved'
            ? ($ticket->resolved_at ?? now())
            : null;
        $updates['closed_at'] = $validated['status'] === 'closed'
            ? ($ticket->closed_at ?? now())
            : null;

        $ticket->update($updates);

        ClientPortalNotification::create([
            'client_id' => $ticket->client_id,
            'type' => 'ticket_status',
            'title' => 'Status tiket diperbarui',
            'message' => "Status tiket {$ticket->ticket_number} sekarang {$ticket->status}.",
            'payload' => [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'status' => $ticket->status,
            ],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tiket berhasil diperbarui.',
            ]);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Tiket berhasil diperbarui.');
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'author_type' => 'staff',
            'message' => $validated['message'],
        ]);

        if ($ticket->first_response_at === null) {
            $ticket->update(['first_response_at' => now()]);
        }

        if (! in_array($ticket->status, ['resolved', 'closed'], true)) {
            $ticket->update(['status' => 'waiting_client']);
        }

        ClientPortalNotification::create([
            'client_id' => $ticket->client_id,
            'type' => 'ticket_reply',
            'title' => 'Ada balasan baru pada tiket Anda',
            'message' => "Tiket {$ticket->ticket_number} mendapat balasan baru dari tim support.",
            'payload' => [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'reply_id' => $reply->id,
            ],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Balasan berhasil dikirim.',
            ]);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Balasan berhasil dikirim.');
    }
}
