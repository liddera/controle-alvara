<?php

namespace App\Models;

use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDispatchMessage extends Model
{
    use HasFactory, HasOwner;

    protected $fillable = [
        'document_dispatch_id',
        'owner_id',
        'documento_id',
        'provider',
        'channel',
        'message_type',
        'provider_message_id',
        'provider_reference',
        'provider_status_raw',
        'current_status',
        'status_rank',
        'destination_email',
        'destination_phone',
        'sent_at',
        'delivered_at',
        'opened_at',
        'failed_at',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function dispatch()
    {
        return $this->belongsTo(DocumentDispatch::class, 'document_dispatch_id');
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function events()
    {
        return $this->hasMany(DocumentDispatchEvent::class)->latest('id');
    }
}
