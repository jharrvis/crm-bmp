<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'client_id',
        'subscription_id',
        'created_by_portal_account_id',
        'assigned_to',
        'ticket_number',
        'subject',
        'category',
        'queue',
        'priority',
        'status',
        'message',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'client_last_read_at',
        'staff_last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'client_last_read_at' => 'datetime',
            'staff_last_read_at' => 'datetime',
        ];
    }

    protected string $activitylogEntityName = 'tiket';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function createdByPortalAccount()
    {
        return $this->belongsTo(ClientPortalAccount::class, 'created_by_portal_account_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function activities()
    {
        return $this->hasMany(TicketActivity::class)->latest();
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'TCK-' . now()->format('ymd') . '-';
        $latest = self::query()
            ->where('ticket_number', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        if (! $latest) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($latest->ticket_number, -4);

        return $prefix . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }
}
