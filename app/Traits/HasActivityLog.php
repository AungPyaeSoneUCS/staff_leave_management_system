<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait HasActivityLog
{
    public static function bootHasActivityLog(): void
    {
        static::created(function ($model) {
            static::log($model, 'created');
        });

        static::updated(function ($model) {
            static::log($model, 'updated');
        });

        static::deleted(function ($model) {
            static::log($model, 'deleted');
        });
    }

    protected static function log($model, string $action, ?array $properties = null): void
    {
        if (! method_exists($model, 'activityLogs')) {
            return;
        }

        $request = request();

        ActivityLog::create([
            'user_id' => $request?->user()?->id,
            'action' => $action,
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'properties' => $properties ?? $model->getDirty(),
            'created_at' => now(),
        ]);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
