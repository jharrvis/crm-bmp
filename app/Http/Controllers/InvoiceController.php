<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Models\Client;
use App\Models\Package;
use App\Mail\InvoiceDeliveryMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:invoices.view')->only(['index', 'show']);
        $this->middleware('permission:invoices.create')->only(['create', 'store', 'generate']);
        $this->middleware('permission:invoices.update')->only(['edit', 'update', 'send']);
        $this->middleware('permission:invoices.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $view = $request->string('view', 'all')->toString();

        if (! in_array($view, ['all', 'draft', 'unpaid', 'paid', 'overdue', 'cancelled'], true)) {
            $view = 'all';
        }

        $invoices = Invoice::query()
            ->with(['client.primaryContact', 'client.contacts'])
            ->latest('invoice_date')
            ->latest('id')
            ->get();

        $today = Carbon::today();
        $nextThirtyDays = Carbon::today()->addDays(30);
        $allInvoices = Invoice::query()->get();

        $summaryCounts = [
            'total' => $allInvoices->count(),
            'draft' => $allInvoices->where('status', 'draft')->count(),
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
        [$clients, $packages, $existingSignatures] = $this->getManualInvoiceFormData();
        $invoice = null;

        return view('invoices.create', compact('clients', 'packages', 'existingSignatures', 'invoice'));
    }

    public function edit(Invoice $invoice)
    {
        [$clients, $packages, $existingSignatures] = $this->getManualInvoiceFormData();
        $invoice->loadMissing(['client.primaryContact', 'client.contacts', 'items']);

        return view('invoices.create', compact('clients', 'packages', 'existingSignatures', 'invoice'));
    }

    /**
     * Store a newly created resource in storage.
     * Manual Invoice Creation
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $payload = $this->buildManualInvoicePayload($request);

            $invoice = Invoice::create([
                'client_id' => $payload['client']->id,
                'invoice_number' => Invoice::generateInvoiceNumber($payload['branchCode']),
                'invoice_date' => $payload['invoiceDate'],
                'due_date' => $payload['dueDate'],
                'subtotal_amount' => $payload['subtotalAmount'],
                'uses_tax' => $payload['usesTax'],
                'tax_rate' => $payload['taxRate'],
                'tax_amount' => $payload['taxAmount'],
                'discount_amount' => $payload['discountAmount'],
                'total_amount' => $payload['totalAmount'],
                'status' => $payload['status'],
                'notes' => $request->notes,
                'signature_path' => $payload['signaturePath'],
            ]);

            foreach ($payload['lineItems'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'subscription_id' => $item['subscription_id'],
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'qty' => $item['qty'],
                    'total' => $item['total'],
                ]);
            }

            $whatsappUrl = null;

            if ($payload['submitAction'] === 'send') {
                [$emailSent, $whatsappSent, $whatsappUrl] = $this->dispatchInvoiceDelivery($invoice, $payload['client'], $request);

                if ($emailSent || $whatsappSent) {
                    $invoice->update([
                        'sent_at' => now(),
                        'sent_via_email' => $emailSent,
                        'sent_via_whatsapp' => $whatsappSent,
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Invoice berhasil dibuat.', 'invoice' => $invoice]);
            }

            $successMessage = match ($payload['submitAction']) {
                'draft' => 'Invoice draft berhasil disimpan.',
                'send' => 'Invoice berhasil disimpan dan diproses untuk dikirim.',
                default => 'Invoice berhasil disimpan dan dikonfirmasi.',
            };

            return redirect()
                ->route('invoices.index')
                ->with('success', $successMessage)
                ->with('invoice_whatsapp_url', $whatsappUrl);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal membuat invoice: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Gagal membuat invoice: ' . $e->getMessage());
        }
    }

    protected function buildManualInvoicePayload(Request $request, ?Invoice $invoice = null): array
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date_mode' => ['required', Rule::in(['7', '14', '30', 'custom'])],
            'due_date' => 'required|date',
            'uses_tax' => 'nullable|boolean',
            'discount_amount' => 'nullable|numeric|min:0',
            'signature_mode' => ['nullable', Rule::in(['none', 'existing', 'upload'])],
            'existing_signature' => 'nullable|string',
            'signature_upload' => 'nullable|image|max:2048',
            'submit_action' => ['required', Rule::in(['draft', 'confirm', 'send'])],
            'items' => 'required|array|min:1',
            'items.*.source' => 'nullable|in:subscription,package,manual',
            'items.*.subscription_id' => 'nullable|exists:subscriptions,id',
            'items.*.package_id' => 'nullable|exists:packages,id',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
            'send_channels' => 'nullable|array|min:1',
            'send_channels.*' => ['string', Rule::in(['email', 'whatsapp'])],
            'email_subject' => 'nullable|string|max:255',
            'email_body' => 'nullable|string',
            'whatsapp_body' => 'nullable|string',
        ]);

        $submitAction = $request->string('submit_action')->toString();
        $existingSignatures = collect($this->getExistingSignatureOptions())->pluck('path')->all();
        $signatureMode = $request->string('signature_mode', 'none')->toString();

        if ($signatureMode === 'existing' && $request->filled('existing_signature') && ! in_array($request->string('existing_signature')->toString(), $existingSignatures, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['existing_signature' => 'Tanda tangan yang dipilih tidak tersedia lagi.']);
        }

        if ($signatureMode === 'upload' && ! $request->hasFile('signature_upload') && ! $invoice?->signature_path) {
            throw \Illuminate\Validation\ValidationException::withMessages(['signature_upload' => 'Unggah file tanda tangan terlebih dahulu.']);
        }

        if ($submitAction === 'send' && collect($request->input('send_channels', []))->filter()->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['send_channels' => 'Pilih minimal satu kanal pengiriman.']);
        }

        $client = Client::with(['branch', 'primaryContact', 'contacts', 'subscriptions'])->findOrFail($request->client_id);
        $branchCode = $client->branch ? $client->branch->code : 'GEN';
        $clientSubscriptionIds = $client->subscriptions()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $invoiceDate = Carbon::parse($request->invoice_date)->startOfDay();
        $dueDate = Carbon::parse($request->due_date)->startOfDay();

        if ($dueDate->lt($invoiceDate)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['due_date' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal invoice.']);
        }

        $subtotalAmount = 0;
        $lineItems = [];

        foreach ($request->items as $item) {
            $lineTotal = round(((float) $item['amount']) * ((int) $item['qty']), 2);
            $subtotalAmount += $lineTotal;
            $lineItems[] = [
                'subscription_id' => isset($item['subscription_id']) && in_array((int) $item['subscription_id'], $clientSubscriptionIds, true)
                    ? (int) $item['subscription_id']
                    : null,
                'description' => $item['description'],
                'amount' => (float) $item['amount'],
                'qty' => (int) $item['qty'],
                'total' => $lineTotal,
            ];
        }

        $usesTax = $request->boolean('uses_tax');
        $taxRate = $usesTax ? 11.0 : null;
        $taxAmount = $usesTax ? round($subtotalAmount * 0.11, 2) : 0.0;
        $discountAmount = round((float) ($request->discount_amount ?? 0), 2);
        $totalAmount = max(0, round($subtotalAmount + $taxAmount - $discountAmount, 2));
        $signaturePath = $this->resolveSignaturePath($request, $signatureMode, $invoice?->signature_path);
        $status = $submitAction === 'draft' ? 'draft' : 'unpaid';

        return compact(
            'client',
            'branchCode',
            'invoiceDate',
            'dueDate',
            'subtotalAmount',
            'usesTax',
            'taxRate',
            'taxAmount',
            'discountAmount',
            'totalAmount',
            'signaturePath',
            'status',
            'lineItems',
            'submitAction',
        );
    }

    protected function getManualInvoiceFormData(): array
    {
        $clients = Client::query()
            ->with([
                'primaryContact',
                'contacts',
                'subscriptions' => function ($query) {
                    $query->where('status', 'active')
                        ->with(['package.service'])
                        ->orderBy('subscription_code');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'client_code', 'address', 'city']);

        $packages = Package::query()
            ->with('service')
            ->where('is_active', true)
            ->whereHas('service', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get(['id', 'service_id', 'name', 'price']);

        return [$clients, $packages, $this->getExistingSignatureOptions()];
    }

    protected function getExistingSignatureOptions(): array
    {
        return collect(Storage::disk('public')->files('invoice-signatures'))
            ->filter(function (string $path) {
                return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'webp'], true);
            })
            ->map(function (string $path) {
                return [
                    'path' => $path,
                    'name' => pathinfo($path, PATHINFO_FILENAME),
                    'url' => Storage::disk('public')->url($path),
                ];
            })
            ->values()
            ->all();
    }

    protected function resolveSignaturePath(Request $request, string $signatureMode, ?string $currentSignaturePath = null): ?string
    {
        if ($signatureMode === 'upload' && $request->hasFile('signature_upload')) {
            return $request->file('signature_upload')->store('invoice-signatures', 'public');
        }

        if ($signatureMode === 'existing' && $request->filled('existing_signature')) {
            return $request->string('existing_signature')->toString();
        }

        if ($signatureMode === 'none') {
            return null;
        }

        return $currentSignaturePath;
    }

    protected function dispatchInvoiceDelivery(Invoice $invoice, Client $client, Request $request): array
    {
        $channels = collect($request->input('send_channels', []))->filter()->values();
        $emailSent = false;
        $whatsappSent = false;
        $whatsappUrl = null;
        $primaryContact = $client->primaryContact ?: $client->contacts->first();

        if ($channels->contains('email')) {
            $recipientEmail = $primaryContact?->email;

            if (! $recipientEmail) {
                throw new \RuntimeException('Pelanggan belum memiliki email kontak untuk pengiriman invoice.');
            }

            Mail::to($recipientEmail)->send(new InvoiceDeliveryMail(
                $invoice->loadMissing('client.primaryContact'),
                $request->string('email_subject')->toString(),
                $request->string('email_body')->toString()
            ));

            $emailSent = true;
        }

        if ($channels->contains('whatsapp')) {
            $rawWhatsapp = $primaryContact?->whatsapp ?: $primaryContact?->phone;

            if (! $rawWhatsapp) {
                throw new \RuntimeException('Pelanggan belum memiliki nomor WhatsApp untuk pengiriman invoice.');
            }

            $normalizedWhatsapp = preg_replace('/[^0-9]/', '', $rawWhatsapp);

            if ($normalizedWhatsapp === '') {
                throw new \RuntimeException('Nomor WhatsApp pelanggan tidak valid.');
            }

            $whatsappSent = true;
            $whatsappUrl = 'https://wa.me/' . $normalizedWhatsapp . '?text=' . urlencode($request->string('whatsapp_body')->toString());
        }

        return [$emailSent, $whatsappSent, $whatsappUrl];
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
        if ($request->has('status') && ! $request->has('client_id')) {
            $invoice->status = $request->status;
            if ($request->status == 'paid' && !$invoice->paid_at) {
                $invoice->paid_at = now();
            }
            if ($request->status == 'unpaid') {
                $invoice->paid_at = null;
            }
            $invoice->save();

            return response()->json(['success' => true, 'message' => 'Status invoice diperbarui.']);
        }

        try {
            DB::beginTransaction();
            $payload = $this->buildManualInvoicePayload($request, $invoice);

            $invoice->update([
                'client_id' => $payload['client']->id,
                'invoice_date' => $payload['invoiceDate'],
                'due_date' => $payload['dueDate'],
                'subtotal_amount' => $payload['subtotalAmount'],
                'uses_tax' => $payload['usesTax'],
                'tax_rate' => $payload['taxRate'],
                'tax_amount' => $payload['taxAmount'],
                'discount_amount' => $payload['discountAmount'],
                'total_amount' => $payload['totalAmount'],
                'status' => $payload['status'],
                'notes' => $request->notes,
                'signature_path' => $payload['signaturePath'],
            ]);

            $invoice->items()->delete();
            foreach ($payload['lineItems'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'subscription_id' => $item['subscription_id'],
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'qty' => $item['qty'],
                    'total' => $item['total'],
                ]);
            }

            $whatsappUrl = null;

            if ($payload['submitAction'] === 'send') {
                [$emailSent, $whatsappSent, $whatsappUrl] = $this->dispatchInvoiceDelivery($invoice->fresh('client.primaryContact', 'client.contacts'), $payload['client'], $request);
                if ($emailSent || $whatsappSent) {
                    $invoice->update([
                        'sent_at' => now(),
                        'sent_via_email' => $emailSent,
                        'sent_via_whatsapp' => $whatsappSent,
                    ]);
                }
            }

            DB::commit();

            $successMessage = match ($payload['submitAction']) {
                'draft' => 'Draft invoice berhasil diperbarui.',
                'send' => 'Invoice berhasil diperbarui dan diproses untuk dikirim.',
                default => 'Invoice berhasil diperbarui.',
            };

            return redirect()
                ->route('invoices.index')
                ->with('success', $successMessage)
                ->with('invoice_whatsapp_url', $whatsappUrl);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui invoice: ' . $e->getMessage());
        }
    }

    public function send(Request $request, Invoice $invoice)
    {
        $request->validate([
            'send_channels' => 'required|array|min:1',
            'send_channels.*' => ['string', Rule::in(['email', 'whatsapp'])],
            'email_subject' => 'nullable|string|max:255',
            'email_body' => 'nullable|string',
            'whatsapp_body' => 'nullable|string',
        ]);

        try {
            $invoice->loadMissing(['client.primaryContact', 'client.contacts']);
            [$emailSent, $whatsappSent, $whatsappUrl] = $this->dispatchInvoiceDelivery($invoice, $invoice->client, $request);

            if ($emailSent || $whatsappSent) {
                $invoice->update([
                    'status' => $invoice->status === 'draft' ? 'unpaid' : $invoice->status,
                    'sent_at' => now(),
                    'sent_via_email' => $emailSent,
                    'sent_via_whatsapp' => $whatsappSent,
                ]);
            }

            return redirect()
                ->route('invoices.index')
                ->with('success', 'Invoice berhasil diproses untuk dikirim.')
                ->with('invoice_whatsapp_url', $whatsappUrl);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim invoice: ' . $e->getMessage());
        }
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
