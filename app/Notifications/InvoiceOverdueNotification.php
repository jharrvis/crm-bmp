<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public int $daysAfterDue
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

        $msg = (new MailMessage)
            ->error()
            ->subject("Pemberitahuan Keterlambatan: Tagihan {$this->invoice->invoice_number}")
            ->greeting("Yth. {$clientName},")
            ->line("Tagihan Anda nomor **{$this->invoice->invoice_number}** sebesar **Rp {$amount}** telah melewati batas waktu pembayaran selama {$this->daysAfterDue} hari (Jatuh tempo: {$dueDate}).");

        if ($this->daysAfterDue >= 7) {
            $msg->line('Layanan Anda mungkin telah/akan ditangguhkan karena keterlambatan pembayaran ini.');
        }

        $msg->line('Mohon segera melakukan pembayaran agar layanan Anda tetap aktif.')
            ->line('Jika Anda sudah melakukan pembayaran, mohon hubungi tim support kami dengan menyertakan bukti transfer.');

        return $msg;
    }
}
