<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ContentActivityObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->record($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted');
    }

    private function record(Model $model, string $action): void
    {
        $name = null;

        foreach (['name', 'title', 'question', 'customer_name', 'original_name'] as $attribute) {
            if (filled($model->getAttribute($attribute))) {
                $name = (string) $model->getAttribute($attribute);
                break;
            }
        }

        ActivityLog::record(
            $action,
            class_basename($model),
            $name ?: class_basename($model).' #'.$model->getKey(),
        );
    }
}
