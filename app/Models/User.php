<?php

namespace App\Models;

use App\Traits\Auditable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use Auditable, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super-admin');
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_token',
        'google_refresh_token',
        'google_token_expires_at',
        'google_calendar_id',
        'plan_id',
        'parent_id',
        'owner_id',
        'is_active',
        'deactivated_at',
        'last_login_at',
        'profile_photo_path',
    ];

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path ? Storage::disk(config('filesystems.default'))->url($this->profile_photo_path) : null;
    }

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
            'google_token_expires_at' => 'datetime',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function hasGoogleConnection(): bool
    {
        return filled($this->google_id) && filled($this->google_token);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
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

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function members()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
