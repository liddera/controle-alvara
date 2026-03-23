<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'alvara_id',
        'google_event_id',
        'tipo_evento',
        'data_evento',
    ];

    protected $casts = [
        'data_evento' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alvara()
    {
        return $this->belongsTo(Alvara::class);
    }
}
