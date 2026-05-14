<?php

namespace App\Services;

use App\Models\ClientPortalAccount;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;

class TicketActivityService
{
    public function recordStaff(Ticket $ticket, string $action, string $description, ?User $user = null, array $properties = []): void
    {
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'actor_type' => 'staff',
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
        ]);
    }

    public function recordClient(Ticket $ticket, string $action, string $description, ?ClientPortalAccount $account = null, array $properties = []): void
    {
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'client_portal_account_id' => $account?->id,
            'actor_type' => 'client',
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
        ]);
    }
}
