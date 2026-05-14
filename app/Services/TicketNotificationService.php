<?php

namespace App\Services;

use App\Mail\TicketActivityMail;
use App\Models\ClientPortalAccount;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class TicketNotificationService
{
    public function sendClientTicketCreated(Ticket $ticket): void
    {
        $account = $this->clientPortalAccount($ticket);

        if (! $account) {
            return;
        }

        $this->sendToClient(
            $account,
            new TicketActivityMail(
                subjectLine: "Tiket {$ticket->ticket_number} berhasil dibuat",
                headline: 'Tiket support Anda sudah tercatat',
                recipientName: $ticket->client->name,
                ticketNumber: $ticket->ticket_number,
                ticketSubject: $ticket->subject,
                messageBody: 'Tim support kami telah menerima tiket Anda dan akan segera menindaklanjuti.',
                actionUrl: $this->portalTicketUrl($ticket),
                actionLabel: 'Lihat Ticket'
            )
        );
    }

    public function sendClientReplyPosted(Ticket $ticket): void
    {
        $account = $this->clientPortalAccount($ticket);

        if (! $account) {
            return;
        }

        $this->sendToClient(
            $account,
            new TicketActivityMail(
                subjectLine: "Balasan baru untuk tiket {$ticket->ticket_number}",
                headline: 'Ada balasan baru dari tim support',
                recipientName: $ticket->client->name,
                ticketNumber: $ticket->ticket_number,
                ticketSubject: $ticket->subject,
                messageBody: 'Silakan buka detail ticket di portal client untuk melihat balasan terbaru.',
                actionUrl: $this->portalTicketUrl($ticket),
                actionLabel: 'Buka Ticket'
            )
        );
    }

    public function sendClientStatusChanged(Ticket $ticket): void
    {
        $account = $this->clientPortalAccount($ticket);

        if (! $account) {
            return;
        }

        $this->sendToClient(
            $account,
            new TicketActivityMail(
                subjectLine: "Status tiket {$ticket->ticket_number} diperbarui",
                headline: 'Status ticket Anda berubah',
                recipientName: $ticket->client->name,
                ticketNumber: $ticket->ticket_number,
                ticketSubject: $ticket->subject,
                messageBody: 'Status terbaru ticket Anda adalah ' . str_replace('_', ' ', $ticket->status) . '.',
                actionUrl: $this->portalTicketUrl($ticket),
                actionLabel: 'Lihat Status Ticket'
            )
        );
    }

    public function sendStaffTicketCreated(Ticket $ticket): void
    {
        $recipients = $this->staffRecipients($ticket);

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(
                new TicketActivityMail(
                    subjectLine: "Tiket baru {$ticket->ticket_number}",
                    headline: 'Ada tiket support baru',
                    recipientName: $recipient->name,
                    ticketNumber: $ticket->ticket_number,
                    ticketSubject: $ticket->subject,
                    messageBody: 'Sebuah tiket baru dibuat oleh client portal dan menunggu tindak lanjut tim support.',
                    actionUrl: $this->crmTicketUrl($ticket),
                    actionLabel: 'Buka Ticket di CRM'
                )
            );
        }
    }

    public function sendStaffClientReply(Ticket $ticket): void
    {
        $recipients = $this->staffRecipients($ticket);

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(
                new TicketActivityMail(
                    subjectLine: "Balasan client pada tiket {$ticket->ticket_number}",
                    headline: 'Ada balasan baru dari client',
                    recipientName: $recipient->name,
                    ticketNumber: $ticket->ticket_number,
                    ticketSubject: $ticket->subject,
                    messageBody: 'Client telah menambahkan balasan baru pada ticket ini. Silakan cek CRM untuk tindak lanjut.',
                    actionUrl: $this->crmTicketUrl($ticket),
                    actionLabel: 'Lihat Ticket di CRM'
                )
            );
        }
    }

    public function sendStaffTicketReopened(Ticket $ticket): void
    {
        $recipients = $this->staffRecipients($ticket);

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(
                new TicketActivityMail(
                    subjectLine: "Tiket {$ticket->ticket_number} dibuka kembali",
                    headline: 'Client membuka kembali tiket',
                    recipientName: $recipient->name,
                    ticketNumber: $ticket->ticket_number,
                    ticketSubject: $ticket->subject,
                    messageBody: 'Ticket yang sebelumnya selesai kini dibuka kembali oleh client dan membutuhkan tindak lanjut.',
                    actionUrl: $this->crmTicketUrl($ticket),
                    actionLabel: 'Tindak Lanjuti Ticket'
                )
            );
        }
    }

    private function clientPortalAccount(Ticket $ticket): ?ClientPortalAccount
    {
        $ticket->loadMissing('client.portalAccount');

        $account = $ticket->client?->portalAccount;

        if (! $account || ! $account->isActive() || blank($account->email)) {
            return null;
        }

        return $account;
    }

    private function staffRecipients(Ticket $ticket): Collection
    {
        $ticket->loadMissing('assignedUser');

        if ($ticket->assignedUser && filled($ticket->assignedUser->email)) {
            return collect([$ticket->assignedUser]);
        }

        return User::role(['Owner', 'Admin', 'Employee'])
            ->whereNotNull('email')
            ->orderBy('name')
            ->get();
    }

    private function sendToClient(ClientPortalAccount $account, TicketActivityMail $mail): void
    {
        Mail::to($account->email)->send($mail);
    }

    private function portalTicketUrl(Ticket $ticket): string
    {
        return rtrim((string) config('client_portal.app_url'), '/') . '/tickets/' . $ticket->id;
    }

    private function crmTicketUrl(Ticket $ticket): string
    {
        return route('tickets.show', $ticket);
    }
}
