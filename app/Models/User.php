<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_token',
        'google_refresh_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }

    public function alvaras()
    {
        return $this->hasMany(Alvara::class);
    }

    public function alertConfigs()
    {
        return $this->hasMany(AlertConfig::class);
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class);
    }
}
