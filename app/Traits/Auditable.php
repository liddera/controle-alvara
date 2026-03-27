<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected array $auditHiddenFields = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

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
            if (empty($newValues)) {
                return;
            }
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($event === 'deleted') {
            $oldValues = $this->getAttributes();
        }

        $oldValues = $this->sanitizeAuditPayload($oldValues);
        $newValues = $this->sanitizeAuditPayload($newValues);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'url' => Request::url(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    protected function sanitizeAuditPayload(array $payload): array
    {
        foreach ($this->auditHiddenFields as $field) {
            unset($payload[$field]);
        }

        return $payload;
    }

    public function audits()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
