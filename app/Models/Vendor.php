<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'name',
        'cid',
        'address',
        'notes',
    ];

    protected string $activitylogEntityName = 'vendor';

    public function contacts()
    {
        return $this->hasMany(VendorContact::class);
    }
}
