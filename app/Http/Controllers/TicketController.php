<?php

namespace App\Http\Controllers;

use App\Models\ClientPortalNotification;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\TicketReplyAttachment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
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
        $clients = Client::query()
            ->with(['subscriptions.package.service'])
            ->orderBy('name')
            ->get();
        $staffUsers = User::role(['Owner', 'Admin', 'Employee'])
            ->orderBy('name')
            ->get();

        return view('tickets.index', compact('tickets', 'clients', 'staffUsers'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'subject' => 'required|string|max:255',
            'category' => ['required', Rule::in(['connectivity', 'billing', 'technical', 'general'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'assigned_to' => 'nullable|exists:users,id',
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,txt',
        ]);

        $subscription = null;
        if (! empty($validated['subscription_id'])) {
            $subscription = Subscription::query()
                ->where('id', $validated['subscription_id'])
                ->where('client_id', $validated['client_id'])
                ->first();

            if (! $subscription) {
                $message = 'Layanan yang dipilih tidak sesuai dengan client.';

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return redirect()->back()->withErrors(['subscription_id' => $message])->withInput();
            }
        }

        $ticket = DB::transaction(function () use ($validated, $subscription, $request) {
            $ticket = Ticket::create([
                'client_id' => $validated['client_id'],
                'subscription_id' => $subscription?->id,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'ticket_number' => Ticket::generateTicketNumber(),
                'subject' => $validated['subject'],
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'status' => 'open',
                'message' => $validated['message'],
            ]);

            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'author_type' => 'staff',
                'message' => $validated['message'],
            ]);

            $this->storeReplyAttachments($reply, $request->file('attachments', []));

            ClientPortalNotification::create([
                'client_id' => $ticket->client_id,
                'type' => 'ticket_created',
                'title' => 'Tiket support baru dibuat',
                'message' => "Tiket {$ticket->ticket_number} telah dibuat oleh tim support.",
                'payload' => [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                ],
            ]);

            return $ticket;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tiket berhasil dibuat.',
                'ticket_id' => $ticket->id,
            ], 201);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Tiket berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'client.primaryContact',
            'subscription.package.service',
            'assignedUser',
            'replies.attachments',
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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,txt',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'author_type' => 'staff',
            'message' => $validated['message'],
        ]);

        $this->storeReplyAttachments($reply, $request->file('attachments', []));

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

    private function storeReplyAttachments(TicketReply $reply, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('ticket-attachments', 'public');

            TicketReplyAttachment::create([
                'ticket_reply_id' => $reply->id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => (int) $file->getSize(),
            ]);
        }
    }
}
