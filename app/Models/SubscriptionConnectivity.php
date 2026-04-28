<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionConnectivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'router_id',
        'ip_address',
        'ip_type',
        'pppoe_user',
        'pppoe_secret',
        'ont_sn',
        'router_model',
        'vlan_id',
        'signal_rx',
        'zabbix_group_id',
        'zabbix_group_name',
        'zabbix_host_id',
        'zabbix_host_name',
        'zabbix_interfaces',
        // Metro Ethernet
        'metro_ethernet_id',
    ];

    protected $casts = [
        'zabbix_interfaces' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function metroEthernet()
    {
        return $this->belongsTo(MetroEthernet::class);
    }
}
