<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingServer extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'name',
        'host',
        'port',
        'api_endpoint',
        'username',
        'api_key',
        'secret_key',
        'type',
        'location',
        'max_accounts',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_key' => 'encrypted',
        'secret_key' => 'encrypted',
    ];

    /**
     * Credentials must never be included in JSON responses used by edit modals.
     */
    protected $hidden = [
        'api_key',
        'secret_key',
    ];

    protected array $activitylogExcludeAttributes = [
        'api_key',
        'secret_key',
    ];

    protected string $activitylogEntityName = 'hosting server';
}
