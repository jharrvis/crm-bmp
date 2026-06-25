<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'service_id',
        'name',
        'price',
        'bandwidth_down',
        'bandwidth_up',
        'quota',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected string $activitylogEntityName = 'package';

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
