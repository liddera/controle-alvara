<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alvara extends Model
{
    use HasFactory;
    protected $fillable = [
        'empresa_id',
        'user_id',
        'tipo',
        'numero',
        'data_emissao',
        'data_vencimento',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'data_vencimento' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class);
    }
}
