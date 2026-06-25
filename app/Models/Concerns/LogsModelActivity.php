<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->activityLogName())
            ->logOnly($this->activityLogAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => $this->activityDescription($eventName));
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->properties = $activity->properties
            ->put('subject_label', $this->activitySubjectLabel())
            ->put('event_label', $this->activityDescription($eventName));
    }

    protected function activityLogName(): string
    {
        if (property_exists($this, 'activitylogName') && filled($this->activitylogName)) {
            return $this->activitylogName;
        }

        return $this->getTable();
    }

    protected function activityLogAttributes(): array
    {
        $attributes = property_exists($this, 'fillable') ? $this->fillable : [];
        $excluded = property_exists($this, 'activitylogExcludeAttributes') ? $this->activitylogExcludeAttributes : [];

        return array_values(array_diff($attributes, $excluded));
    }

    protected function activityDescription(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'Menambahkan ' . $this->activityEntityName(),
            'updated' => 'Memperbarui ' . $this->activityEntityName(),
            'deleted' => 'Menghapus ' . $this->activityEntityName(),
            default => ucfirst($eventName) . ' ' . $this->activityEntityName(),
        };
    }

    protected function activityEntityName(): string
    {
        if (property_exists($this, 'activitylogEntityName') && filled($this->activitylogEntityName)) {
            return $this->activitylogEntityName;
        }

        return strtolower(class_basename($this));
    }

    protected function activitySubjectLabel(): string
    {
        foreach (['name', 'title', 'ticket_number', 'invoice_number', 'subscription_code', 'client_code', 'email', 'code', 'cid'] as $attribute) {
            if (filled($this->{$attribute} ?? null)) {
                return (string) $this->{$attribute};
            }
        }

        return class_basename($this) . ' #' . $this->getKey();
    }
}
