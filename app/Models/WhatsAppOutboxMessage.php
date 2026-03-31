<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppOutboxMessage extends Model
{
    protected $table = 'whatsapp_outbox_messages';

    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'owner_id',
        'instance_key',
        'type',
        'to',
        'payload',
        'provider_message_id',
        'status',
        'attempts',
        'last_error',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];
}

