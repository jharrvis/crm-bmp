<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'name',
        'description',
    ];

    protected string $activitylogEntityName = 'division';

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
