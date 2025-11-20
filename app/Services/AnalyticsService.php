<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\PosSaleItem;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function salesMix(?int $branchId = null, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $query = PosSaleItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as total_sales'))
            ->groupBy('product_id')
            ->whereHas('sale', function ($sale) use ($branchId, $from, $to) {
                if ($branchId) {
                    $sale->where('branch_id', $branchId);
                }

                if ($from) {
                    $sale->where('sold_at', '>=', $from);
                }

                if ($to) {
                    $sale->where('sold_at', '<=', $to);
                }
            });

        $totalSales = (clone $query)->get()->sum(fn ($row) => $row->total_sales);

        return $query->with('product')->get()->map(function ($row) use ($totalSales) {
            return [
                'product_id' => $row->product_id,
                'product_name' => $row->product?->name,
                'quantity' => (int) $row->total_qty,
                'sales' => (float) $row->total_sales,
                'mix_percentage' => $totalSales > 0 ? round(($row->total_sales / $totalSales) * 100, 2) : 0,
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function stockAging(?int $branchId = null): Collection
    {
        $query = ProductBatch::query()
            ->select('product_id', 'batch_number', 'expiry_date', 'quantity', 'branch_id')
            ->where('quantity', '>', 0)
            ->with('product', 'branch');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $now = Carbon::now();

        return $query->get()->map(function ($batch) use ($now) {
            $daysToExpiry = $batch->expiry_date ? $now->diffInDays(Carbon::parse($batch->expiry_date), false) : null;

            return [
                'product_id' => $batch->product_id,
                'product_name' => $batch->product?->name,
                'branch_id' => $batch->branch_id,
                'branch_name' => $batch->branch?->name,
                'batch_number' => $batch->batch_number,
                'quantity' => (int) $batch->quantity,
                'expiry_date' => optional($batch->expiry_date)?->toDateString(),
                'days_to_expiry' => $daysToExpiry,
                'status' => $daysToExpiry === null ? 'no_expiry' : ($daysToExpiry < 0 ? 'expired' : ($daysToExpiry <= 30 ? 'near_expiry' : 'healthy')),
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function demandForecast(?int $branchId = null, int $windowDays = 30, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $startDate = Carbon::now()->subDays($windowDays);

        if ($from && $from->greaterThan($startDate)) {
            $startDate = $from;
        }

        $movements = InventoryMovement::query()
            ->select('product_id', 'branch_id', DB::raw('DATE(performed_at) as date'), DB::raw('SUM(quantity) as qty'))
            ->where('type', 'sale')
            ->where('performed_at', '>=', $startDate)
            ->groupBy('product_id', 'branch_id', DB::raw('DATE(performed_at)'));

        if ($branchId) {
            $movements->where('branch_id', $branchId);
        }

        if ($to) {
            $movements->where('performed_at', '<=', $to);
        }

        $grouped = $movements->get()->groupBy('product_id');

        return $grouped->map(function ($rows, $productId) {
            $daily = $rows->groupBy('date')->map(fn ($day) => abs($day->sum('qty')));
            $average = $daily->avg() ?? 0;
            $projected = round($average * 7, 2);

            return [
                'product_id' => (int) $productId,
                'average_daily_sales' => round($average, 2),
                'projected_weekly_demand' => $projected,
            ];
        })->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function alerts(?int $branchId = null): Collection
    {
        $alerts = collect();

        $stockAging = $this->stockAging($branchId);
        $nearExpiry = $stockAging->where('status', 'near_expiry');
        if ($nearExpiry->isNotEmpty()) {
            $quantity = $nearExpiry->sum('quantity');
            $alerts->push([
                'type' => 'inventory.near_expiry',
                'severity' => 'warning',
                'message' => sprintf('%d batch mendekati kedaluwarsa.', $nearExpiry->count()),
                'meta' => [
                    'batches' => $nearExpiry->take(5)->values(),
                    'total_quantity' => $quantity,
                ],
            ]);
        }

        $expired = $stockAging->where('status', 'expired');
        if ($expired->isNotEmpty()) {
            $alerts->push([
                'type' => 'inventory.expired',
                'severity' => 'critical',
                'message' => sprintf('%d batch telah kedaluwarsa.', $expired->count()),
                'meta' => [
                    'batches' => $expired->take(5)->values(),
                ],
            ]);
        }

        $forecast = $this->demandForecast($branchId);
        $forecast->filter(fn ($row) => $row['projected_weekly_demand'] >= 100)->each(function ($row) use (&$alerts) {
            $alerts->push([
                'type' => 'sales.demand_spike',
                'severity' => 'info',
                'message' => sprintf('Permintaan produk #%d diproyeksikan %s unit per minggu.', $row['product_id'], $row['projected_weekly_demand']),
                'meta' => $row,
            ]);
        });

        $topSales = $this->salesMix($branchId)->sortByDesc('sales')->take(5);
        if ($topSales->isNotEmpty()) {
            $alerts->push([
                'type' => 'sales.top_products',
                'severity' => 'info',
                'message' => 'Produk terlaris minggu ini.',
                'meta' => $topSales->values(),
            ]);
        }

        return $alerts->values();
    }
}
