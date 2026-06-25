<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientContact extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'client_id',
        'name',
        'position',
        'email',
        'phone',
        'whatsapp',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected string $activitylogEntityName = 'kontak pelanggan';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
