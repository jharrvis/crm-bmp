<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingServerSnapshot extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'hosting_server_id',
        'summary_json',
        'status',
        'last_synced_at',
        'error_message',
        'is_active',
    ];

    protected $casts = [
        'summary_json' => 'array',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected string $activitylogEntityName = 'hosting server snapshot';

    public function hostingServer()
    {
        return $this->belongsTo(HostingServer::class);
    }
}