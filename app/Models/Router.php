<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    use HasFactory, LogsModelActivity;

    public const ROLE_OPTIONS = [
        'core' => 'Core',
        'pop' => 'POP',
        'distribution' => 'Distribusi',
        'access' => 'Akses',
        'customer_gateway' => 'Customer Gateway',
        'management' => 'Management',
        'other' => 'Lainnya',
    ];

    protected $fillable = [
        'branch_id',
        'name',
        'host',
        'port',
        'user',
        'password',
        'type',
        'router_role',
        'custom_role',
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
