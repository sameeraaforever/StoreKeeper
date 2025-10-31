<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ModelAuditor
{
    /**
     * Called after model is created
     */
    public function created(Model $model)
    {
        $this->logChanges($model, 'created', $model->getAttributes(), []);
    }

    /**
     * Called before update, but we'll use updated (after) to get both old/new
     */
    public function updated(Model $model)
    {
        $changes = $model->getChanges();
        $original = $model->getOriginal();

        foreach ($changes as $field => $newValue) {
            $old = $original[$field] ?? null;
            AuditLog::create([
                'user_id' => Auth::id(),
                'table_name' => $model->getTable(),
                'record_id' => $model->getKey(),
                'field_name' => $field,
                'old_value' => is_scalar($old) ? (string)$old : json_encode($old),
                'new_value' => is_scalar($newValue) ? (string)$newValue : json_encode($newValue),
                'action' => 'updated',
                'meta' => json_encode([
                    'model' => get_class($model),
                ]),
            ]);
        }
    }

    /**
     * Called when deleting (soft or hard)
     */
    public function deleting(Model $model)
    {
        // If soft deleting, record a deleted action for all current attributes
        if (method_exists($model, 'getDeletedAtColumn')) {
            // mark delete action
            $this->logChanges($model, 'deleted', [], $model->getAttributes());
        }
    }

    /**
     * Called on restore (soft deletes)
     */
    public function restored(Model $model)
    {
        $this->logChanges($model, 'restored', [], []);
    }

    protected function logChanges(Model $model, string $action, array $new = [], array $old = [])
    {
        $userId = Auth::id();
        $table = $model->getTable();
        $recordId = $model->getKey();

        // If created, store new attributes per field
        if ($action === 'created') {
            foreach ($new as $field => $value) {
                AuditLog::create([
                    'user_id' => $userId,
                    'table_name' => $table,
                    'record_id' => $recordId,
                    'field_name' => $field,
                    'old_value' => null,
                    'new_value' => is_scalar($value) ? (string)$value : json_encode($value),
                    'action' => 'created',
                    'meta' => json_encode(['model' => get_class($model)]),
                ]);
            }
            return;
        }

        // For deleted/restored/logging full record state
        if ($action === 'deleted' || $action === 'restored') {
            AuditLog::create([
                'user_id' => $userId,
                'table_name' => $table,
                'record_id' => $recordId,
                'field_name' => null,
                'old_value' => json_encode($old),
                'new_value' => json_encode($new),
                'action' => $action,
                'meta' => json_encode(['model' => get_class($model)]),
            ]);
            return;
        }
    }
}
