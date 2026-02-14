<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model): void {
            AuditLog::record(
                action: 'created',
                entityType: get_class($model),
                entityId: $model->id,
                old: null,
                new: $model->getAttributes(),
            );
        });

        static::updated(function (self $model): void {
            $changes = collect($model->getChanges())
                ->except('updated_at')
                ->toArray();

            if ($changes === []) {
                return;
            }

            $oldValues = collect($changes)
                ->keys()
                ->mapWithKeys(fn (string $key): array => [$key => $model->getOriginal($key)])
                ->toArray();

            AuditLog::record(
                action: 'updated',
                entityType: get_class($model),
                entityId: $model->id,
                old: $oldValues,
                new: $changes,
            );
        });

        static::deleted(function (self $model): void {
            AuditLog::record(
                action: 'deleted',
                entityType: get_class($model),
                entityId: $model->id,
                old: $model->getAttributes(),
                new: null,
            );
        });
    }

    public function auditLog(): Builder
    {
        return AuditLog::where('entity_type', get_class($this))
            ->where('entity_id', $this->id)
            ->orderByDesc('created_at');
    }
}
