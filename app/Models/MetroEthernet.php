<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetroEthernet extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'cid',
        'ip_address',
        'bandwidth',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
