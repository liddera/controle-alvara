<?php

namespace App\Models;

use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertConfig extends Model
{
    use HasOwner;

    protected $fillable = [
        'owner_id',
        'user_id',
        'tipo_alvara_id',
        'days_before',
        'is_active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tipoAlvara(): BelongsTo
    {
        return $this->belongsTo(TipoAlvara::class);
    }
}
