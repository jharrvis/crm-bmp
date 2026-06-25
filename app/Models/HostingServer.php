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

    protected array $activitylogExcludeAttributes = [
        'api_key',
        'secret_key',
    ];

    protected string $activitylogEntityName = 'hosting server';
}
