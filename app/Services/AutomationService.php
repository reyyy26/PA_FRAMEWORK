<?php

namespace App\Services;

use App\Models\ProductBranchSetting;
use App\Models\ProductBatch;
use Illuminate\Support\Collection;

class AutomationService
{
    public function __construct(private readonly NotificationService $notifier, private readonly AuditService $audit)
    {
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function evaluateReorder(bool $dispatchNotifications = true): Collection
    {
        $candidates = ProductBranchSetting::query()
            ->with('product:id,sku,name')
            ->where('reorder_point', '>', 0)
            ->get()
            ->map(function (ProductBranchSetting $setting) {
                $current = (int) ProductBatch::query()
                    ->where('product_id', $setting->product_id)
                    ->where('branch_id', $setting->branch_id)
                    ->sum('quantity');

                if ($current >= $setting->reorder_point) {
                    return null;
                }

                $recommended = max($setting->reorder_qty, $setting->reorder_point - $current);

                return [
                    'branch_id' => $setting->branch_id,
                    'product_id' => $setting->product_id,
                    'product_name' => $setting->product?->name,
                    'current_quantity' => $current,
                    'reorder_point' => $setting->reorder_point,
                    'recommended_quantity' => $recommended,
                ];
            })
            ->filter()
            ->values();

        if ($dispatchNotifications && $candidates->isNotEmpty()) {
            $this->notifier->notify(['email', 'slack'], [
                'type' => 'inventory.auto_reorder_suggestions',
                'items' => $candidates->take(10)->toArray(),
                'generated_at' => now()->toIso8601String(),
            ]);

            $this->audit->log('automation.reorder_evaluated', null, [
                'suggestions' => $candidates->count(),
            ]);
        }

        return $candidates;
    }
}
