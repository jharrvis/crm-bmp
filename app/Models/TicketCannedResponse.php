<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketCannedResponse extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'message',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected string $activitylogEntityName = 'template respons tiket';
}
