<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TicketReplyAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_reply_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    protected $appends = [
        'public_url',
    ];

    public function ticketReply()
    {
        return $this->belongsTo(TicketReply::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return url(Storage::disk($this->disk)->url($this->path));
    }
}
