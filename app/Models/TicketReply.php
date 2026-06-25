<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'ticket_id',
        'client_portal_account_id',
        'user_id',
        'author_type',
        'is_internal',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    protected array $activitylogExcludeAttributes = [
        'message',
    ];

    protected string $activitylogEntityName = 'balasan tiket';

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function portalAccount()
    {
        return $this->belongsTo(ClientPortalAccount::class, 'client_portal_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketReplyAttachment::class);
    }
}
