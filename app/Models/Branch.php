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
    ];

    protected string $activitylogEntityName = 'branch';

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
