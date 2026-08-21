<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionDomain extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'subscription_id',
        'domain_name',
        'registrar',
        'auth_code_encrypted',
        'registered_at',
        'expires_at',
        'dns_records',
        'notes',
        'registrar_account_id',
        'provider_domain_id',
        'provider_status',
        'provider_metadata',
        'last_synced_at',
        'sync_status',
        'sync_error_summary',
        'managed_by_crm',
        'domain_account_mode',
        'not_found_at',
        'managed_dns_enabled',
    ];

    protected $casts = [
        'registered_at' => 'date',
        'expires_at' => 'date',
        'dns_records' => 'array',
        'provider_metadata' => 'array',
        'last_synced_at' => 'datetime',
        'not_found_at' => 'datetime',
        'managed_by_crm' => 'boolean',
        'managed_dns_enabled' => 'boolean',
    ];

    protected $hidden = [
        'auth_code_encrypted',
        'domain_name_lower',
    ];

    protected array $activitylogExcludeAttributes = [
        'auth_code_encrypted',
    ];

    protected string $activitylogEntityName = 'domain';

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function registrarAccount(): BelongsTo
    {
        return $this->belongsTo(RegistrarAccount::class);
    }

    public function registrarOperations(): HasMany
    {
        return $this->hasMany(RegistrarOperation::class, 'subscription_domain_id');
    }

    /**
     * Get the decrypted auth code (stored via encrypt()).
     */
    public function getAuthCodeAttribute(): ?string
    {
        return $this->auth_code_encrypted ? decrypt($this->auth_code_encrypted) : null;
    }
}
