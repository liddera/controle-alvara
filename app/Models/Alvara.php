<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOwner;
use App\DTOs\AlvaraFilterDTO;
use App\Traits\Auditable;

class Alvara extends Model
{
    use HasFactory, Auditable, HasOwner;
    protected $fillable = [
        'empresa_id',
        'user_id',
        'owner_id',
        'tipo_alvara_id',
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

    public function tipoAlvara()
    {
        return $this->belongsTo(TipoAlvara::class, 'tipo_alvara_id');
    }

    public function scopeVigente($query)
    {
        return $query->where('status', 'vigente');
    }

    public function scopeEmRenovacao($query)
    {
        return $query->where('status', 'proximo');
    }

    public function scopeVencido($query)
    {
        return $query->where('status', 'vencido');
    }

    /**
     * Scope a query to filter alvaras based on a DTO.
     */
    public function scopeFilterByDto($query, AlvaraFilterDTO $dto)
    {
        return $query->when($dto->empresa_id, fn($q) => $q->where('empresa_id', $dto->empresa_id))
            ->when($dto->search, fn($q) => $q->where('tipo', 'like', '%' . $dto->search . '%'))
            ->when($dto->status && $dto->status !== 'todos', fn($q) => $q->where('status', $dto->status));
    }
}
