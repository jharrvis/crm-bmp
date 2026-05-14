<?php

namespace App\Services;

use App\Models\Ticket;

class TicketCannedResponseRenderer
{
    public function renderForTicket(Ticket $ticket, string $template): string
    {
        $ticket->loadMissing([
            'client.branch',
            'client.primaryContact',
            'subscription.package.service',
            'subscription.connectivity.metroEthernet',
        ]);

        $client = $ticket->client;
        $contact = $client?->primaryContact;
        $subscription = $ticket->subscription;
        $connectivity = $subscription?->connectivity;

        $replacements = [
            '{{client_name}}' => $client?->name ?? '-',
            '{{client_code}}' => $client?->client_code ?? '-',
            '{{branch_name}}' => $client?->branch?->name ?? '-',
            '{{primary_contact_name}}' => $contact?->name ?? '-',
            '{{primary_contact_phone}}' => $contact?->phone ?? ($contact?->whatsapp ?? '-'),
            '{{primary_contact_email}}' => $contact?->email ?? '-',
            '{{ticket_number}}' => $ticket->ticket_number ?? '-',
            '{{ticket_subject}}' => $ticket->subject ?? '-',
            '{{ticket_category}}' => $ticket->category ?? '-',
            '{{ticket_priority}}' => $ticket->priority ?? '-',
            '{{ticket_status}}' => $ticket->status ?? '-',
            '{{queue_name}}' => config('tickets.queues.' . $ticket->queue, $ticket->queue ?? '-'),
            '{{subscription_code}}' => $subscription?->subscription_code ?? '-',
            '{{package_name}}' => $subscription?->package?->name ?? '-',
            '{{service_name}}' => $subscription?->package?->service?->name ?? '-',
            '{{installed_at}}' => $subscription?->installed_at?->format('d-m-Y') ?? '-',
            '{{next_billing_date}}' => $subscription?->next_billing_date?->format('d-m-Y') ?? '-',
            '{{effective_price}}' => $subscription ? 'Rp ' . number_format((float) $subscription->effective_price, 0, ',', '.') : '-',
            '{{ip_address}}' => $connectivity?->ip_address ?? '-',
            '{{pppoe_user}}' => $connectivity?->pppoe_user ?? '-',
            '{{router_model}}' => $connectivity?->router_model ?? '-',
            '{{metro_name}}' => $connectivity?->metroEthernet?->name ?? '-',
            '{{zabbix_host_name}}' => $connectivity?->zabbix_host_name ?? '-',
        ];

        return strtr($template, $replacements);
    }
}
