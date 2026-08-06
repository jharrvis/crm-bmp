<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'default_province_code',
        'default_regency_code',
        'default_latitude',
        'default_longitude',
    ];

    protected $casts = [
        'default_latitude' => 'decimal:8',
        'default_longitude' => 'decimal:8',
    ];

    protected string $activitylogEntityName = 'branch';

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
