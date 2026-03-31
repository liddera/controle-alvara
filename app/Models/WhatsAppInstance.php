<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppInstance extends Model
{
    protected $table = 'whatsapp_instances';

    protected $fillable = [
        'owner_id',
        'provider',
        'instance_key',
        'instance_id',
        'instance_api_key',
        'status',
        'last_qr_code_base64',
        'last_pairing_code',
        'last_qr_code_payload',
        'last_connection_state',
        'last_webhook_at',
        'connected_at',
    ];

    protected $casts = [
        'instance_api_key' => 'encrypted',
        'last_webhook_at' => 'datetime',
        'connected_at' => 'datetime',
    ];
}
