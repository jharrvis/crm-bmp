<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public int $daysBeforeDue
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->invoice->due_date?->format('d M Y');
        $amount = number_format($this->invoice->total_amount, 0, ',', '.');
        $clientName = $this->invoice->client->name;

        return (new MailMessage)
            ->subject("Reminder Tagihan: {$this->invoice->invoice_number}")
            ->greeting("Yth. {$clientName},")
            ->line("Ini adalah pengingat bahwa tagihan Anda nomor **{$this->invoice->invoice_number}** sebesar **Rp {$amount}** akan jatuh tempo dalam {$this->daysBeforeDue} hari pada tanggal **{$dueDate}**.")
            ->line('Mohon segera melakukan pembayaran agar layanan Anda tidak terganggu.')
            ->line('Jika Anda sudah melakukan pembayaran, mohon abaikan email ini.')
            ->line('Terima kasih telah menggunakan layanan kami.');
    }
}
