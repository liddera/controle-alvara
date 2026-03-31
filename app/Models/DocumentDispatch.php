<?php

namespace App\Models;

use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDispatch extends Model
{
    use HasFactory, HasOwner;

    protected $fillable = [
        'owner_id',
        'alvara_id',
        'requested_by_user_id',
        'trigger_type',
        'channel',
        'destination_name',
        'destination_email',
        'destination_phone',
        'current_status',
        'status_rank',
        'requested_at',
        'last_event_at',
        'summary_payload',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'last_event_at' => 'datetime',
        'summary_payload' => 'array',
    ];

    public function alvara()
    {
        return $this->belongsTo(Alvara::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function messages()
    {
        return $this->hasMany(DocumentDispatchMessage::class)->latest('id');
    }

    public function events()
    {
        return $this->hasMany(DocumentDispatchEvent::class)->latest('id');
    }
}
