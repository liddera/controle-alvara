<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertConfig extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'dias_antes',
        'tipo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
