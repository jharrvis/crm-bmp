<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrarAccount extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'provider',
        'name',
        'is_active',
        'base_url',
        'api_username_encrypted',
        'api_password_encrypted',
        'settings_encrypted',
        'last_tested_at',
        'last_synced_at',
        'last_error_at',
        'last_error_summary',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_username_encrypted' => 'encrypted',
        'api_password_encrypted' => 'encrypted',
        'settings_encrypted' => 'encrypted:array',
        'last_tested_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    protected $hidden = [
        'api_username_encrypted',
        'api_password_encrypted',
        'settings_encrypted',
    ];

    protected array $activitylogExcludeAttributes = [
        'api_username_encrypted',
        'api_password_encrypted',
        'settings_encrypted',
    ];

    protected string $activitylogEntityName = 'registrar account';

    public function subscriptionDomains(): HasMany
    {
        return $this->hasMany(SubscriptionDomain::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(RegistrarOperation::class);
    }

    /**
     * Get allowed TLDs from settings, e.g. [".com", ".co.id"].
     */
    public function allowedTlds(): array
    {
        $settings = $this->settings_encrypted ?? [];
        return $settings['allowed_tlds'] ?? [];
    }
}
