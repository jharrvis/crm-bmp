<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\ClientPortalAccount;
use App\Models\ClientPortalNotification;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\TicketReplyAttachment;
use App\Services\TicketNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ClientPortalTicketController extends Controller
{
    public function __construct(
        private readonly TicketNotificationService $ticketNotificationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'])],
        ]);

        $query = Ticket::query()
            ->with(['subscription.package.service'])
            ->withCount([
                'replies as unread_client_replies_count' => fn ($replyQuery) => $replyQuery
                    ->where('author_type', 'staff')
                    ->where('is_internal', false)
                    ->whereRaw('ticket_replies.created_at > COALESCE(tickets.client_last_read_at, tickets.created_at)'),
            ])
            ->where('client_id', $account->client_id)
            ->latest();

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return response()->json([
            'data' => $query->get()->map(fn (Ticket $ticket) => $this->serializeTicketSummary($ticket)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        $validated = $request->validate([
            'subscription_id' => 'nullable|integer',
            'subject' => 'required|string|max:255',
            'category' => ['required', Rule::in(['connectivity', 'billing', 'technical', 'general'])],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,txt',
        ]);

        $subscription = null;
        if (! empty($validated['subscription_id'])) {
            $subscription = Subscription::query()
                ->where('id', $validated['subscription_id'])
                ->where('client_id', $account->client_id)
                ->firstOrFail();
        }

        $ticket = DB::transaction(function () use ($account, $subscription, $validated, $request) {
            $ticket = Ticket::create([
                'client_id' => $account->client_id,
                'subscription_id' => $subscription?->id,
                'created_by_portal_account_id' => $account->id,
                'ticket_number' => Ticket::generateTicketNumber(),
                'subject' => $validated['subject'],
                'category' => $validated['category'],
                'priority' => $validated['priority'] ?? 'normal',
                'status' => 'open',
                'message' => $validated['message'],
                'client_last_read_at' => now(),
            ]);

            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'client_portal_account_id' => $account->id,
                'author_type' => 'client',
                'is_internal' => false,
                'message' => $validated['message'],
            ]);

            $this->storeReplyAttachments($reply, $request->file('attachments', []));

            ClientPortalNotification::create([
                'client_id' => $account->client_id,
                'type' => 'ticket_created',
                'title' => 'Tiket berhasil dibuat',
                'message' => "Tiket {$ticket->ticket_number} telah dibuat dan menunggu penanganan.",
                'payload' => [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                ],
            ]);

            return $ticket;
        });

        $ticket->load(['subscription.package.service', 'replies.attachments', 'replies.portalAccount']);

        $this->ticketNotificationService->sendStaffTicketCreated($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil dibuat.',
            'data' => $this->serializeTicketDetail($ticket),
        ], 201);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $ticket = $this->authorizedTicket($request, $ticket);
        $ticket->forceFill([
            'client_last_read_at' => now(),
        ])->save();

        $ticket->load(['subscription.package.service', 'replies.attachments', 'replies.portalAccount', 'replies.user', 'assignedUser']);

        return response()->json([
            'data' => $this->serializeTicketDetail($ticket),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();
        $ticket = $this->authorizedTicket($request, $ticket);

        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            return response()->json([
                'message' => 'Tiket yang sudah resolved atau closed tidak dapat dibalas dari portal client.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,txt',
        ]);

        $reply = DB::transaction(function () use ($account, $ticket, $validated, $request) {
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'client_portal_account_id' => $account->id,
                'author_type' => 'client',
                'is_internal' => false,
                'message' => $validated['message'],
            ]);

            $this->storeReplyAttachments($reply, $request->file('attachments', []));

            $ticket->forceFill([
                'status' => 'open',
                'client_last_read_at' => now(),
            ])->save();

            ClientPortalNotification::create([
                'client_id' => $account->client_id,
                'type' => 'ticket_reply',
                'title' => 'Balasan tiket terkirim',
                'message' => "Balasan baru berhasil dikirim ke tiket {$ticket->ticket_number}.",
                'payload' => [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                ],
            ]);

            return $reply;
        });

        $reply->load(['attachments', 'portalAccount', 'user']);

        $this->ticketNotificationService->sendStaffClientReply($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Balasan tiket berhasil dikirim.',
            'reply' => $this->serializeReply($reply),
        ], 201);
    }

    public function reopen(Request $request, Ticket $ticket): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();
        $ticket = $this->authorizedTicket($request, $ticket);
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        if (! in_array($ticket->status, ['resolved', 'closed'], true)) {
            return response()->json([
                'message' => 'Hanya tiket resolved atau closed yang dapat dibuka kembali.',
            ], 422);
        }

        DB::transaction(function () use ($account, $ticket, $validated) {
            TicketReply::create([
                'ticket_id' => $ticket->id,
                'client_portal_account_id' => $account->id,
                'author_type' => 'client',
                'is_internal' => false,
                'message' => "Client membuka kembali tiket ini.\n\nAlasan reopen:\n" . $validated['reason'],
            ]);

            $ticket->forceFill([
                'status' => 'open',
                'resolved_at' => null,
                'closed_at' => null,
                'client_last_read_at' => now(),
            ])->save();
        });

        $this->ticketNotificationService->sendStaffTicketReopened($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil dibuka kembali.',
        ]);
    }

    private function authorizedTicket(Request $request, Ticket $ticket): Ticket
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        abort_unless($ticket->client_id === $account->client_id, 404);

        return $ticket;
    }

    private function serializeTicketSummary(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'category' => $ticket->category,
            'priority' => $ticket->priority,
            'status' => $ticket->status,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'unread_replies_count' => (int) ($ticket->unread_client_replies_count ?? 0),
            'subscription' => $ticket->subscription ? [
                'id' => $ticket->subscription->id,
                'subscription_code' => $ticket->subscription->subscription_code,
                'package_name' => $ticket->subscription->package?->name,
                'service_name' => $ticket->subscription->package?->service?->name,
            ] : null,
        ];
    }

    private function serializeTicketDetail(Ticket $ticket): array
    {
        return [
            ...$this->serializeTicketSummary($ticket),
            'message' => $ticket->message,
            'assigned_to' => $ticket->assignedUser ? [
                'id' => $ticket->assignedUser->id,
                'name' => $ticket->assignedUser->name,
            ] : null,
            'first_response_at' => $ticket->first_response_at?->toIso8601String(),
            'resolved_at' => $ticket->resolved_at?->toIso8601String(),
            'closed_at' => $ticket->closed_at?->toIso8601String(),
            'replies' => $ticket->replies
                ->where('is_internal', false)
                ->map(fn (TicketReply $reply) => $this->serializeReply($reply))
                ->values(),
        ];
    }

    private function serializeReply(TicketReply $reply): array
    {
        return [
            'id' => $reply->id,
            'author_type' => $reply->author_type,
            'message' => $reply->message,
            'author_name' => match ($reply->author_type) {
                'client' => $reply->portalAccount?->client?->name ?? 'Client',
                'staff' => $reply->user?->name ?? 'Staff',
                default => 'System',
            },
            'created_at' => $reply->created_at?->toIso8601String(),
            'attachments' => $reply->attachments->map(fn (TicketReplyAttachment $attachment) => [
                'id' => $attachment->id,
                'name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size_bytes' => (int) $attachment->size_bytes,
                'url' => $attachment->public_url,
            ])->values(),
        ];
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
