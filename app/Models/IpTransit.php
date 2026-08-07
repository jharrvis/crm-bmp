<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpTransit extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'vendor_id',
        'name',
        'cid',
        'ip_address',
        'ip_gateway',
        'as_number',
        'bandwidth',
    ];

    protected $casts = [
        'bandwidth' => 'integer',
    ];

    protected string $activitylogEntityName = 'IP transit';

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
