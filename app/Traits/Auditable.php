<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->recordAudit('created');
        });

        static::updated(function ($model) {
            $model->recordAudit('updated');
        });

        static::deleted(function ($model) {
            $model->recordAudit('deleted');
        });
    }

    public function recordAudit(string $event)
    {
        $oldValues = [];
        $newValues = [];

        if ($event === 'updated') {
            $newValues = $this->getDirty();
            foreach ($newValues as $key => $value) {
                $oldValues[$key] = $this->getOriginal($key);
            }
            
            // Se nada mudou (raro mas possível), não loga
            if (empty($newValues)) return;
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
            // Remove campos sensíveis
            unset($newValues['password'], $newValues['remember_token']);
        } elseif ($event === 'deleted') {
            $oldValues = $this->getAttributes();
            unset($oldValues['password'], $oldValues['remember_token']);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function audits()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
