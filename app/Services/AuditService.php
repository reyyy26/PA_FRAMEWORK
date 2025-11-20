<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * @param array<string, mixed> $changes
     */
    public function log(string $action, ?Model $model = null, array $changes = []): void
    {
        /** @var Authenticatable|null $user */
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->getAuthIdentifier(),
            'action' => $action,
            'model_type' => $model ? $model::class : 'system',
            'model_id' => $model?->getKey(),
            'changes' => empty($changes) ? null : $changes,
        ]);
    }
}
