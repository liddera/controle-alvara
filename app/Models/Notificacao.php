<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    use HasFactory;
    protected $table = 'notificacoes';

    protected $fillable = [
        'user_id',
        'alvara_id',
        'tipo',
        'mensagem',
        'lida',
        'data_envio',
    ];

    protected $casts = [
        'lida' => 'boolean',
        'data_envio' => 'datetime',
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
