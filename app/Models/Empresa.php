<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasOwner;

use App\Traits\Auditable;

class Empresa extends Model
{
    use HasFactory, Auditable, HasOwner;
    protected $fillable = [
        'user_id',
        'owner_id',
        'nome',
        'cnpj',
        'responsavel',
        'telefone',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alvaras()
    {
        return $this->hasMany(Alvara::class);
    }

    public function tiposAlvara()
    {
        return $this->belongsToMany(TipoAlvara::class, 'empresa_tipo_alvara');
    }
}
