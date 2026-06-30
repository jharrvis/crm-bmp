<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MarkOverdueInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $updatedCount = Invoice::whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => 'overdue']);

        if ($updatedCount > 0) {
            Log::info("Marked {$updatedCount} invoices as overdue.");
        }
    }
}
