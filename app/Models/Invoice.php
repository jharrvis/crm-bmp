<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'client_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal_amount',
        'uses_tax',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'paid_at',
        'notes',
        'signature_path',
        'sent_at',
        'sent_via_email',
        'sent_via_whatsapp',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal_amount' => 'decimal:2',
        'uses_tax' => 'boolean',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'sent_via_email' => 'boolean',
        'sent_via_whatsapp' => 'boolean',
    ];

    protected string $activitylogEntityName = 'invoice';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function resolveBranch(): ?Branch
    {
        foreach ($this->items as $item) {
            $branch = $item->subscription?->client?->branch;

            if ($branch) {
                return $branch;
            }
        }

        return $this->client?->branch;
    }

    public function calculateBillingSummary(): array
    {
        if ($this->subtotal_amount !== null || $this->tax_amount !== null || $this->discount_amount !== null) {
            $subtotal = (float) ($this->subtotal_amount ?? 0);
            $taxAmount = $this->uses_tax ? (float) ($this->tax_amount ?? 0) : 0.0;
            $discountAmount = (float) ($this->discount_amount ?? 0);

            return [
                'subtotal' => $subtotal,
                'ppn_amount' => $taxAmount,
                'pph23_amount' => 0.0,
                'discount_amount' => $discountAmount,
                'total_amount' => (float) $this->total_amount,
            ];
        }

        $subtotal = 0.0;
        $ppnAmount = 0.0;
        $pph23Amount = 0.0;

        foreach ($this->items as $item) {
            $subtotal += $item->billing_line_total;
            $ppnAmount += $item->billing_ppn_amount;
            $pph23Amount += $item->billing_pph23_amount;
        }

        $calculatedTotal = $subtotal + $ppnAmount - $pph23Amount;
        $storedTotal = (float) $this->total_amount;
        $totalAmount = abs($storedTotal - $calculatedTotal) < 0.01 ? $calculatedTotal : $storedTotal;

        return [
            'subtotal' => $subtotal,
            'ppn_amount' => $ppnAmount,
            'pph23_amount' => $pph23Amount,
            'discount_amount' => 0.0,
            'total_amount' => $totalAmount,
        ];
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if (! $this->signature_path) {
            return null;
        }

        return Storage::disk('public')->url($this->signature_path);
    }

    public function getAmountInWordsAttribute(): string
    {
        return Str::title(trim($this->spellNumber((int) round((float) $this->total_amount)))) . ' rupiah';
    }

    private function spellNumber(int $value): string
    {
        $value = abs($value);
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($value === 0) {
            return 'nol';
        }

        if ($value < 12) {
            return $words[$value];
        }

        if ($value < 20) {
            return $this->spellNumber($value - 10) . ' belas';
        }

        if ($value < 100) {
            $remainder = $value % 10;
            return $this->spellNumber((int) floor($value / 10)) . ' puluh' . ($remainder > 0 ? ' ' . $this->spellNumber($remainder) : '');
        }

        if ($value < 200) {
            $remainder = $value - 100;
            return 'seratus' . ($remainder > 0 ? ' ' . $this->spellNumber($remainder) : '');
        }

        if ($value < 1000) {
            $remainder = $value % 100;
            return $this->spellNumber((int) floor($value / 100)) . ' ratus' . ($remainder > 0 ? ' ' . $this->spellNumber($remainder) : '');
        }

        if ($value < 2000) {
            $remainder = $value - 1000;
            return 'seribu' . ($remainder > 0 ? ' ' . $this->spellNumber($remainder) : '');
        }

        if ($value < 1000000) {
            $remainder = $value % 1000;
            return $this->spellNumber((int) floor($value / 1000)) . ' ribu' . ($remainder > 0 ? ' ' . $this->spellNumber($remainder) : '');
        }

        if ($value < 1000000000) {
            $remainder = $value % 1000000;
            return $this->spellNumber((int) floor($value / 1000000)) . ' juta' . ($remainder > 0 ? ' ' . $this->spellNumber($remainder) : '');
        }

        if ($value < 1000000000000) {
            $remainder = $value % 1000000000;
            return $this->spellNumber((int) floor($value / 1000000000)) . ' miliar' . ($remainder > 0 ? ' ' . $this->spellNumber($remainder) : '');
        }

        $remainder = $value % 1000000000000;
        return $this->spellNumber((int) floor($value / 1000000000000)) . ' triliun' . ($remainder > 0 ? ' ' . $this->spellNumber($remainder) : '');
    }

    // Generate Invoice Number helper
    public static function generateInvoiceNumber($branchCode)
    {
        $yearMonth = now()->format('ym');
        $prefix = "INV-{$branchCode}-{$yearMonth}-";

        $latest = self::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if (!$latest) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($latest->invoice_number, -4);
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
