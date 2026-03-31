<?php

namespace App\Models;

use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDispatchEvent extends Model
{
    use HasFactory, HasOwner;

    protected $fillable = [
        'owner_id',
        'document_dispatch_id',
        'document_dispatch_message_id',
        'provider',
        'event_name',
        'event_key',
        'provider_message_id',
        'normalized_status',
        'occurred_at',
        'received_at',
        'payload',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'received_at' => 'datetime',
        'payload' => 'array',
    ];

    public function dispatch()
    {
        return $this->belongsTo(DocumentDispatch::class, 'document_dispatch_id');
    }

    public function message()
    {
        return $this->belongsTo(DocumentDispatchMessage::class, 'document_dispatch_message_id');
    }
}
