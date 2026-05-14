<?php

namespace App\Http\Controllers;

use App\Models\ClientPortalNotification;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\TicketCannedResponse;
use App\Models\TicketReply;
use App\Models\TicketReplyAttachment;
use App\Services\TicketActivityService;
use App\Services\TicketNotificationService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketNotificationService $ticketNotificationService,
        private readonly TicketActivityService $ticketActivityService
    ) {
    }

    public function index(Request $request)
    {
        $ticketQueues = config('tickets.queues', []);

        $query = Ticket::query()
            ->with(['client.primaryContact', 'subscription.package.service', 'assignedUser'])
            ->withCount([
                'replies as unread_staff_replies_count' => fn ($replyQuery) => $replyQuery
                    ->where('author_type', 'client')
                    ->where('is_internal', false)
                    ->whereRaw('ticket_replies.created_at > COALESCE(tickets.staff_last_read_at, tickets.created_at)'),
            ])
            ->latest();

        if ($request->filled('q')) {
            $keyword = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($keyword) {
                $builder
                    ->where('ticket_number', 'like', '%' . $keyword . '%')
                    ->orWhere('subject', 'like', '%' . $keyword . '%')
                    ->orWhere('message', 'like', '%' . $keyword . '%')
                    ->orWhereHas('client', fn ($clientQuery) => $clientQuery
                        ->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('client_code', 'like', '%' . $keyword . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('queue')) {
            $query->where('queue', $request->string('queue'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('assigned_to')) {
            $assignedTo = $request->string('assigned_to')->toString();

            if ($assignedTo === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', (int) $assignedTo);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        $tickets = $query->get();
        $clients = Client::query()
            ->with(['subscriptions.package.service'])
            ->orderBy('name')
            ->get();
        $staffUsers = User::role(['Owner', 'Admin', 'Employee'])
            ->orderBy('name')
            ->get();

        return view('tickets.index', compact('tickets', 'clients', 'staffUsers', 'ticketQueues'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'subject' => 'required|string|max:255',
            'category' => ['required', Rule::in(['connectivity', 'billing', 'technical', 'general'])],
            'queue' => ['nullable', Rule::in(array_keys(config('tickets.queues', [])))],
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
                'queue' => $validated['queue'] ?? $this->defaultQueueForCategory($validated['category']),
                'priority' => $validated['priority'],
                'status' => 'open',
                'message' => $validated['message'],
                'staff_last_read_at' => now(),
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

            $this->ticketActivityService->recordStaff(
                $ticket,
                'created',
                'Ticket dibuat oleh staff.',
                $request->user(),
                [
                    'status' => $ticket->status,
                    'queue' => $ticket->queue,
                    'priority' => $ticket->priority,
                    'assigned_to' => $ticket->assigned_to,
                ]
            );

            return $ticket;
        });

        $this->ticketNotificationService->sendClientTicketCreated($ticket);

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
        $ticket->forceFill([
            'staff_last_read_at' => now(),
        ])->save();

        $ticket->load([
            'client.primaryContact',
            'subscription.package.service',
            'assignedUser',
            'activities.user',
            'activities.portalAccount.client',
            'replies.attachments',
            'replies.portalAccount.client',
            'replies.user',
        ]);

        $staffUsers = User::role(['Owner', 'Admin', 'Employee'])
            ->orderBy('name')
            ->get();

        $ticketQueues = config('tickets.queues', []);
        $cannedResponses = TicketCannedResponse::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('tickets.show', compact('ticket', 'staffUsers', 'ticketQueues', 'cannedResponses'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse|JsonResponse
    {
        $previousStatus = $ticket->status;
        $previousQueue = $ticket->queue;
        $previousPriority = $ticket->priority;
        $previousAssignedTo = $ticket->assigned_to;

        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'])],
            'queue' => ['nullable', Rule::in(array_keys(config('tickets.queues', [])))],
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

        $activityMessages = [];

        if ($previousStatus !== $ticket->status) {
            $activityMessages[] = "status: {$previousStatus} -> {$ticket->status}";
        }

        if ($previousQueue !== $ticket->queue) {
            $activityMessages[] = "queue: " . ($previousQueue ?: 'unassigned') . " -> " . ($ticket->queue ?: 'unassigned');
        }

        if ($previousPriority !== $ticket->priority) {
            $activityMessages[] = "priority: {$previousPriority} -> {$ticket->priority}";
        }

        if ((int) $previousAssignedTo !== (int) $ticket->assigned_to) {
            $previousAssigneeName = $ticket->getRelationValue('assignedUser')?->name;
            $newAssignee = $ticket->assignedUser()->first();
            $activityMessages[] = 'assignee: ' . ($previousAssignedTo ? ($previousAssigneeName ?? 'staff sebelumnya') : 'unassigned') . ' -> ' . ($newAssignee?->name ?? 'unassigned');
        }

        if ($activityMessages !== []) {
            $this->ticketActivityService->recordStaff(
                $ticket,
                'updated',
                'Ticket diperbarui (' . implode(', ', $activityMessages) . ').',
                $request->user(),
                [
                    'from' => [
                        'status' => $previousStatus,
                        'queue' => $previousQueue,
                        'priority' => $previousPriority,
                        'assigned_to' => $previousAssignedTo,
                    ],
                    'to' => [
                        'status' => $ticket->status,
                        'queue' => $ticket->queue,
                        'priority' => $ticket->priority,
                        'assigned_to' => $ticket->assigned_to,
                    ],
                ]
            );
        }

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

        if ($previousStatus !== $ticket->status) {
            $this->ticketNotificationService->sendClientStatusChanged($ticket);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tiket berhasil diperbarui.',
            ]);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Tiket berhasil diperbarui.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:tickets,id',
            'status' => ['nullable', Rule::in(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'])],
            'queue' => ['nullable', Rule::in(array_keys(config('tickets.queues', [])))],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'assigned_to' => 'nullable',
        ]);

        $assignedToValue = $request->input('assigned_to');

        if ($assignedToValue !== null && $assignedToValue !== '' && $assignedToValue !== '__unassigned__') {
            validator(
                ['assigned_to' => $assignedToValue],
                ['assigned_to' => 'exists:users,id']
            )->validate();
        }

        $updates = array_filter([
            'status' => $validated['status'] ?? null,
            'queue' => $validated['queue'] ?? null,
            'priority' => $validated['priority'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if ($assignedToValue === '__unassigned__') {
            $updates['assigned_to'] = null;
        } elseif ($assignedToValue !== null && $assignedToValue !== '') {
            $updates['assigned_to'] = (int) $assignedToValue;
        }

        if ($updates === []) {
            return redirect()
                ->route('tickets.index')
                ->with('error', 'Pilih minimal satu perubahan untuk bulk action.');
        }

        $tickets = Ticket::query()
            ->with('assignedUser')
            ->whereIn('id', $validated['ticket_ids'])
            ->get();

        DB::transaction(function () use ($tickets, $updates, $request) {
            foreach ($tickets as $ticket) {
                $previousStatus = $ticket->status;
                $previousQueue = $ticket->queue;
                $previousPriority = $ticket->priority;
                $previousAssignedTo = $ticket->assigned_to;

                $ticket->fill($updates);

                if (array_key_exists('status', $updates)) {
                    $ticket->resolved_at = $ticket->status === 'resolved'
                        ? ($ticket->resolved_at ?? now())
                        : null;
                    $ticket->closed_at = $ticket->status === 'closed'
                        ? ($ticket->closed_at ?? now())
                        : null;

                    if ($ticket->first_response_at === null && in_array($ticket->status, ['in_progress', 'waiting_client', 'resolved', 'closed'], true)) {
                        $ticket->first_response_at = now();
                    }
                }

                $ticket->save();

                $activityChanges = [];
                if ($previousStatus !== $ticket->status) {
                    $activityChanges[] = "status: {$previousStatus} -> {$ticket->status}";
                }
                if ($previousQueue !== $ticket->queue) {
                    $activityChanges[] = 'queue: ' . ($previousQueue ?: 'unassigned') . ' -> ' . ($ticket->queue ?: 'unassigned');
                }
                if ($previousPriority !== $ticket->priority) {
                    $activityChanges[] = "priority: {$previousPriority} -> {$ticket->priority}";
                }
                if ((int) $previousAssignedTo !== (int) $ticket->assigned_to) {
                    $newAssignee = $ticket->assignedUser()->first();
                    $activityChanges[] = 'assignee: ' . ($previousAssignedTo ?: 'unassigned') . ' -> ' . ($newAssignee?->name ?? 'unassigned');
                }

                if ($activityChanges !== []) {
                    $this->ticketActivityService->recordStaff(
                        $ticket,
                        'bulk_updated',
                        'Bulk update ticket (' . implode(', ', $activityChanges) . ').',
                        $request->user(),
                        [
                            'from' => [
                                'status' => $previousStatus,
                                'queue' => $previousQueue,
                                'priority' => $previousPriority,
                                'assigned_to' => $previousAssignedTo,
                            ],
                            'to' => [
                                'status' => $ticket->status,
                                'queue' => $ticket->queue,
                                'priority' => $ticket->priority,
                                'assigned_to' => $ticket->assigned_to,
                            ],
                        ]
                    );
                }

                if ($previousStatus !== $ticket->status) {
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

                    $this->ticketNotificationService->sendClientStatusChanged($ticket);
                }
            }
        });

        return redirect()
            ->route('tickets.index')
            ->with('success', count($validated['ticket_ids']) . ' tiket berhasil diperbarui.');
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'nullable|boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,txt',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'author_type' => 'staff',
            'is_internal' => (bool) ($validated['is_internal'] ?? false),
            'message' => $validated['message'],
        ]);

        $this->storeReplyAttachments($reply, $request->file('attachments', []));

        if (! $reply->is_internal && $ticket->first_response_at === null) {
            $ticket->update(['first_response_at' => now()]);
        }

        $ticket->forceFill([
            'staff_last_read_at' => now(),
        ])->save();

        if (! $reply->is_internal && ! in_array($ticket->status, ['resolved', 'closed'], true)) {
            $ticket->update(['status' => 'waiting_client']);
        }

        if (! $reply->is_internal) {
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

            $this->ticketNotificationService->sendClientReplyPosted($ticket);
        }

        $this->ticketActivityService->recordStaff(
            $ticket,
            $reply->is_internal ? 'internal_note' : 'reply',
            $reply->is_internal ? 'Staff menambahkan catatan internal.' : 'Staff mengirim balasan ke client.',
            $request->user(),
            [
                'reply_id' => $reply->id,
                'has_attachments' => $reply->attachments()->exists(),
            ]
        );

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

    private function defaultQueueForCategory(string $category): ?string
    {
        return config('tickets.category_queue_map.' . $category);
    }
}
