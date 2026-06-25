<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetroEthernet extends Model
{
    use HasFactory, LogsModelActivity;

    protected $appends = [
        'display_name',
        'selection_label',
    ];

    protected $fillable = [
        'name',
        'vendor_id',
        'cid',
        'ip_address',
        'bandwidth',
    ];

    protected string $activitylogEntityName = 'metro ethernet';

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        if ($this->cid) {
            return 'CID: ' . $this->cid;
        }

        return 'Metro Ethernet #' . $this->id;
    }

    public function getSelectionLabelAttribute(): string
    {
        $parts = [$this->display_name];

        if ($this->vendor?->name) {
            $parts[] = $this->vendor->name;
        }

        if ($this->cid) {
            $parts[] = 'CID: ' . $this->cid;
        }

        if ($this->bandwidth !== null) {
            $parts[] = $this->bandwidth . ' Mbps';
        }

        return implode(' | ', array_unique(array_filter($parts)));
    }

}
