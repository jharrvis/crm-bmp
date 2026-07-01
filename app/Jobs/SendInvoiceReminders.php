<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\SystemSetting;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\InvoiceReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendInvoiceReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function handle(): void
    {
        $daysBefore = SystemSetting::get('billing.reminder_days_before', [7, 3, 1]);
        $daysAfter = SystemSetting::get('billing.reminder_days_after', [1, 7, 14]);
        $channel = SystemSetting::get('billing.reminder_channel', 'email');

        $today = now()->startOfDay();

        // Query unpaid/partially paid and overdue invoices
        $invoices = Invoice::with('client.primaryContact')
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->whereNotNull('due_date')
            ->get();

        $sentCount = 0;

        foreach ($invoices as $invoice) {
            $dueDate = $invoice->due_date->copy()->startOfDay();

            if ($today->lt($dueDate)) {
                // Before Due Date (Reminder)
                $diffDays = $today->diffInDays($dueDate);
                if (in_array($diffDays, $daysBefore)) {
                    if ($this->processReminder($invoice, 'before_due', $diffDays, $channel)) {
                        $sentCount++;
                    }
                }
            } elseif ($today->gt($dueDate)) {
                // After Due Date (Overdue)
                $diffDays = $today->diffInDays($dueDate);
                if (in_array($diffDays, $daysAfter)) {
                    if ($this->processReminder($invoice, 'after_due', $diffDays, $channel)) {
                        $sentCount++;
                    }
                }
            } else {
                // Exact Due Date (Day 0)
                if (in_array(0, $daysBefore)) {
                    if ($this->processReminder($invoice, 'before_due', 0, $channel)) {
                        $sentCount++;
                    }
                }
            }
        }

        if ($sentCount > 0) {
            Log::info("Sent {$sentCount} automatic invoice reminders.");
        }
    }

    protected function processReminder(Invoice $invoice, string $type, int $offset, string $channel): bool
    {
        // Check if already sent
        $exists = InvoiceReminder::where('invoice_id', $invoice->id)
            ->where('reminder_type', $type)
            ->where('days_offset', $offset)
            ->where('status', 'sent')
            ->exists();

        if ($exists) {
            return false;
        }

        $email = $invoice->client->primaryContact?->email;

        try {
            if (($channel === 'email' || $channel === 'both') && $email) {
                if ($type === 'before_due') {
                    Notification::route('mail', $email)->notify(new InvoiceReminderNotification($invoice, $offset));
                } else {
                    Notification::route('mail', $email)->notify(new InvoiceOverdueNotification($invoice, $offset));
                }
            }

            InvoiceReminder::create([
                'invoice_id' => $invoice->id,
                'reminder_type' => $type,
                'days_offset' => $offset,
                'channel' => $channel,
                'sent_at' => now(),
                'status' => 'sent',
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send reminder for invoice {$invoice->id}: ".$e->getMessage());

            InvoiceReminder::updateOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'reminder_type' => $type,
                    'days_offset' => $offset,
                ],
                [
                    'channel' => $channel,
                    'sent_at' => now(),
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]
            );

            return false;
        }
    }
}
