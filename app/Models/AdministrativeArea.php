<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeArea extends Model
{
    public const LEVEL_PROVINCE = 'province';
    public const LEVEL_REGENCY = 'regency';
    public const LEVEL_DISTRICT = 'district';
    public const LEVEL_VILLAGE = 'village';

    public const LEVELS = [
        self::LEVEL_PROVINCE,
        self::LEVEL_REGENCY,
        self::LEVEL_DISTRICT,
        self::LEVEL_VILLAGE,
    ];

    protected $fillable = [
        'code',
        'parent_code',
        'level',
        'name',
    ];
}
