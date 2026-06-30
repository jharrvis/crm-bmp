<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 300; // 5 minutes max

    public function handle(): void
    {
        if (! setting('billing.auto_generate_enabled', false)) {
            Log::info('Auto-generate invoice is disabled.');

            return;
        }

        // Normally ran daily and checks inside schedule(), but we verify here too
        $generateDay = (int) setting('billing.auto_generate_day', 1);
        if (now()->day !== $generateDay) {
            return;
        }

        $activeSubscriptions = Subscription::where('status', 'active')
            ->with(['client.branch', 'package'])
            ->get();

        $generatedCount = 0;
        $dueDays = (int) setting('billing.default_due_days', 7);
        $ppnRate = (float) setting('billing.ppn_rate', 11);

        foreach ($activeSubscriptions as $sub) {
            try {
                DB::transaction(function () use ($sub, $dueDays, $ppnRate, &$generatedCount) {
                    // Check if already generated this month
                    $exists = InvoiceItem::where('subscription_id', $sub->id)
                        ->whereHas('invoice', function ($q) {
                            $q->whereMonth('invoice_date', now()->month)
                                ->whereYear('invoice_date', now()->year);
                        })->exists();

                    if ($exists) {
                        return;
                    }

                    $branchCode = $sub->client->branch->code ?? 'GEN';
                    $usesTax = $sub->uses_ppn;
                    $taxRate = $usesTax ? $ppnRate : null;
                    $taxAmount = $usesTax ? $sub->ppn_amount : 0.0;

                    $invoice = Invoice::create([
                        'client_id' => $sub->client_id,
                        'invoice_number' => Invoice::generateInvoiceNumber($branchCode),
                        'invoice_date' => now(),
                        'due_date' => now()->addDays($dueDays),
                        'subtotal_amount' => $sub->base_price,
                        'uses_tax' => $usesTax,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'discount_amount' => 0.0,
                        'total_amount' => $sub->effective_price,
                        'status' => 'unpaid',
                        'notes' => 'Tagihan otomatis bulanan.',
                    ]);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'subscription_id' => $sub->id,
                        'description' => 'Langganan '.$sub->package->name.' (Periode '.now()->format('F Y').')',
                        'amount' => $sub->base_price,
                        'qty' => 1,
                        'total' => $sub->base_price,
                        'is_prorated' => false,
                    ]);

                    // Update next billing date
                    $nextBillingDate = now()->addMonth()->startOfDay(); // Keep same day naturally, but start of day
                    $sub->update(['next_billing_date' => $nextBillingDate]);

                    $generatedCount++;
                });
            } catch (\Exception $e) {
                Log::error("Failed to generate invoice for subscription {$sub->id}: ".$e->getMessage());
            }
        }

        Log::info("Monthly invoice generation completed. $generatedCount invoices created.");
    }
}
