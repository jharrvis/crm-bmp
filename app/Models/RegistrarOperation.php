<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrarOperation extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'registrar_account_id',
        'subscription_domain_id',
        'operation_type',
        'status',
        'request_payload_redacted',
        'request_secret_encrypted',
        'response_payload_redacted',
        'idempotency_key',
        'requested_by',
        'approved_by',
        'started_at',
        'completed_at',
        'error_summary',
    ];

    protected $casts = [
        'request_payload_redacted' => 'array',
        'response_payload_redacted' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $hidden = [
        'request_secret_encrypted',
    ];

    protected array $activitylogExcludeAttributes = [
        'request_secret_encrypted',
    ];

    protected string $activitylogEntityName = 'registrar operation';

    public function registrarAccount(): BelongsTo
    {
        return $this->belongsTo(RegistrarAccount::class);
    }

    public function subscriptionDomain(): BelongsTo
    {
        return $this->belongsTo(SubscriptionDomain::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
