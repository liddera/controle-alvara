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

    protected function status(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (!$this->data_vencimento) {
                    return $value;
                }
                
                $hoje = now()->startOfDay();
                $vencimento = \Carbon\Carbon::parse($this->data_vencimento)->startOfDay();
                $dias = $hoje->diffInDays($vencimento, false);

                if ($dias < 0) return 'vencido';
                if ($dias <= 30) return 'proximo';
                return 'vigente';
            }
        );
    }

    public function scopeVigente($query)
    {
        $limite = now()->addDays(30)->endOfDay();
        return $query->whereNotNull('data_vencimento')->where('data_vencimento', '>', $limite);
    }

    public function scopeEmRenovacao($query)
    {
        $hoje = now()->startOfDay();
        $limite = now()->addDays(30)->endOfDay();
        return $query->whereNotNull('data_vencimento')->whereBetween('data_vencimento', [$hoje, $limite]);
    }

    public function scopeVencido($query)
    {
        return $query->whereNotNull('data_vencimento')->where('data_vencimento', '<', now()->startOfDay());
    }

    /**
     * Scope a query to filter alvaras based on a DTO.
     */
    public function scopeFilterByDto($query, AlvaraFilterDTO $dto)
    {
        return $query->when($dto->empresa_id, fn($q) => $q->where('empresa_id', $dto->empresa_id))
            ->when($dto->tipo_alvara_id, fn($q) => $q->where('tipo_alvara_id', $dto->tipo_alvara_id))
            ->when($dto->search, fn($q) => $q->where('tipo', 'like', '%' . $dto->search . '%'))
            ->when($dto->status && $dto->status !== 'todos', function($q) use ($dto) {
                if ($dto->status === 'vencido') return $q->vencido();
                if ($dto->status === 'proximo') return $q->emRenovacao();
                if ($dto->status === 'vigente') return $q->vigente();
                
                return $q->where('status', $dto->status);
            });
    }
}
