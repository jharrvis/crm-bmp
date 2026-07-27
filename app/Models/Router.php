<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'branch_id',
        'name',
        'host',
        'port',
        'user',
        'password',
        'type',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'encrypted',
    ];

    protected array $activitylogExcludeAttributes = [
        'password',
    ];

    protected string $activitylogEntityName = 'router';

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function connectivities()
    {
        return $this->hasMany(SubscriptionConnectivity::class);
    }
}
