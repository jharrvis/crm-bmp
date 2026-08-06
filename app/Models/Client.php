<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, LogsModelActivity;

    public const TYPE_OPTIONS = [
        'personal' => 'Personal (Perorangan)',
        'business' => 'Bisnis / Perusahaan',
        'government' => 'Pemerintah / Instansi Publik / BUMN',
        'education' => 'Pendidikan',
        'nonprofit' => 'Yayasan / LSM / Nirlaba',
        'religious' => 'Keagamaan',
        'community' => 'Komunitas / Organisasi / Asosiasi',
        'property' => 'Properti Bersama',
        'other' => 'Lainnya',
    ];

    protected $fillable = [
        'branch_id',
        'user_id',
        'client_code',
        'registered_at',
        'name',
        'type',
        'custom_type',
        'identity_number',
        'address',
        'rt',
        'rw',
        'city',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'notes',
    ];

    protected $casts = [
        'registered_at' => 'date',
    ];

    protected string $activitylogEntityName = 'pelanggan';

    public function getTypeLabelAttribute(): string
    {
        if ($this->type === 'other' && filled($this->custom_type)) {
            return $this->custom_type;
        }

        return self::TYPE_OPTIONS[$this->type] ?? 'Tidak ditentukan';
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    // Helper to get primary contact
    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function portalAccount()
    {
        return $this->hasOne(ClientPortalAccount::class);
    }

    public function province()
    {
        return $this->belongsTo(AdministrativeArea::class, 'province_code', 'code');
    }

    public function regency()
    {
        return $this->belongsTo(AdministrativeArea::class, 'regency_code', 'code');
    }

    public function district()
    {
        return $this->belongsTo(AdministrativeArea::class, 'district_code', 'code');
    }

    public function village()
    {
        return $this->belongsTo(AdministrativeArea::class, 'village_code', 'code');
    }
}
