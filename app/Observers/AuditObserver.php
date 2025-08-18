<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->logAction('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->logAction('updated', $model);
    }

    public function deleted(Model $model): void
    {
        $this->logAction('deleted', $model);
    }

    protected function logAction(string $action, Model $model): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_id' => $model->getKey(),
            'auditable_type' => $model->getMorphClass(),
            'old_values' => $action === 'updated' ? $model->getOriginal() : null,
            'new_values' => $action !== 'deleted' ? $model->getAttributes() : null,
        ]);
    }
}
