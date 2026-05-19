<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:invoices.view')->only(['index', 'show']);
        $this->middleware('permission:invoices.create')->only(['create', 'store', 'generate']);
        $this->middleware('permission:invoices.update')->only(['update']);
        $this->middleware('permission:invoices.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $view = $request->string('view', 'all')->toString();

        if (! in_array($view, ['all', 'unpaid', 'paid', 'overdue', 'cancelled'], true)) {
            $view = 'all';
        }

        $query = Invoice::query()
            ->with('client')
            ->latest('invoice_date')
            ->latest('id');

        if ($view !== 'all') {
            $query->where('status', $view);
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($keyword) {
                $builder
                    ->where('invoice_number', 'like', '%' . $keyword . '%')
                    ->orWhereHas('client', fn ($clientQuery) => $clientQuery
                        ->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('client_code', 'like', '%' . $keyword . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->string('date_to'));
        }

        if ($request->filled('due_from')) {
            $query->whereDate('due_date', '>=', $request->string('due_from'));
        }

        if ($request->filled('due_to')) {
            $query->whereDate('due_date', '<=', $request->string('due_to'));
        }

        $invoices = $query->get();

        $today = Carbon::today();
        $nextThirtyDays = Carbon::today()->addDays(30);
        $allInvoices = Invoice::query()->get();

        $summaryCounts = [
            'total' => $allInvoices->count(),
            'unpaid' => $allInvoices->where('status', 'unpaid')->count(),
            'paid' => $allInvoices->where('status', 'paid')->count(),
            'overdue' => $allInvoices->where('status', 'overdue')->count(),
            'cancelled' => $allInvoices->where('status', 'cancelled')->count(),
        ];

        $averagePaidDays = round(
            $allInvoices
                ->filter(fn (Invoice $invoice) => $invoice->paid_at && $invoice->invoice_date)
                ->avg(fn (Invoice $invoice) => $invoice->invoice_date->diffInDays($invoice->paid_at)) ?? 0
        );

        $overviewMetrics = [
            'overdue_amount' => $allInvoices
                ->filter(fn (Invoice $invoice) => in_array($invoice->status, ['unpaid', 'overdue'], true) && $invoice->due_date && $invoice->due_date->lt($today))
                ->sum(fn (Invoice $invoice) => (float) $invoice->total_amount),
            'due_soon_amount' => $allInvoices
                ->filter(fn (Invoice $invoice) => in_array($invoice->status, ['unpaid', 'overdue'], true) && $invoice->due_date && $invoice->due_date->between($today, $nextThirtyDays))
                ->sum(fn (Invoice $invoice) => (float) $invoice->total_amount),
            'average_paid_days' => $averagePaidDays,
            'paid_this_month_amount' => $allInvoices
                ->filter(fn (Invoice $invoice) => $invoice->status === 'paid' && $invoice->paid_at && $invoice->paid_at->isSameMonth($today))
                ->sum(fn (Invoice $invoice) => (float) $invoice->total_amount),
        ];

        return view('invoices.index', compact('invoices', 'view', 'summaryCounts', 'overviewMetrics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Manual invoice creation is handled via modal or specific flow
        // For now, we can redirect or show a simple view
        return redirect()->route('invoices.index');
    }

    /**
     * Store a newly created resource in storage.
     * Manual Invoice Creation
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $client = Client::findOrFail($request->client_id);
            $branchCode = $client->branch ? $client->branch->code : 'GEN'; // Fallback

            $invoice = Invoice::create([
                'client_id' => $client->id,
                'invoice_number' => Invoice::generateInvoiceNumber($branchCode),
                'invoice_date' => now(),
                'due_date' => $request->due_date,
                'total_amount' => 0, // Calculated below
                'status' => 'unpaid',
                'notes' => $request->notes,
            ]);

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['amount'] * $item['qty'];
                $totalAmount += $subtotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'qty' => $item['qty'],
                    'total' => $subtotal,
                ]);
            }

            $invoice->update(['total_amount' => $totalAmount]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Invoice berhasil dibuat.', 'invoice' => $invoice]);
            }
            return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal membuat invoice: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal membuat invoice.');
        }
    }

    /**
     * Generate Invoices from Active Subscriptions
     * This is usually called via Scheduler or Button
     */
    public function generate()
    {
        $activeSubscriptions = Subscription::where('status', 'active')->with(['client.branch', 'package'])->get();
        $generatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($activeSubscriptions as $sub) {
                // Check if invoice already exists for this month (simple check)
                // Logic: check invoice items linked to this subscription created anywhere in this month
                $exists = InvoiceItem::where('subscription_id', $sub->id)
                    ->whereHas('invoice', function ($q) {
                        $q->whereMonth('invoice_date', now()->month)
                            ->whereYear('invoice_date', now()->year);
                    })->exists();

                if ($exists)
                    continue;

                // Create Invoice
                $branchCode = $sub->client->branch->code ?? 'UNK';
                $invoice = Invoice::create([
                    'client_id' => $sub->client_id,
                    'invoice_number' => Invoice::generateInvoiceNumber($branchCode),
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(7), // Due in 7 days
                    'total_amount' => $sub->effective_price,
                    'status' => 'unpaid',
                    'notes' => null,
                ]);

                // Create Item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'subscription_id' => $sub->id,
                    'description' => "Langganan " . $sub->package->name . " (Periode " . now()->format('F Y') . ")",
                    'amount' => $sub->base_price,
                    'qty' => 1,
                    'total' => $sub->base_price,
                ]);

                $generatedCount++;
            }
            DB::commit();

            return response()->json(['success' => true, 'message' => "Berhasil generate $generatedCount invoice baru."]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['client.branch', 'items.subscription.client.branch', 'items.subscription.package']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     * (Mostly for status update)
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Simple status update for now
        if ($request->has('status')) {
            $invoice->status = $request->status;
            if ($request->status == 'paid' && !$invoice->paid_at) {
                $invoice->paid_at = now();
            }
            if ($request->status == 'unpaid') {
                $invoice->paid_at = null;
            }
            $invoice->save();
        }

        return response()->json(['success' => true, 'message' => 'Status invoice diperbarui.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Invoice $invoice)
    {
        try {
            DB::transaction(function () use ($invoice) {
                $invoice->items()->delete();
                $invoice->delete();
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Invoice berhasil dihapus.']);
            }

            return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dihapus.');
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus invoice.'], 500);
            }

            return redirect()->route('invoices.index')->with('error', 'Gagal menghapus invoice.');
        }
    }
}
