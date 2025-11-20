<?php

namespace App\Services;

use App\Models\IntegrationLog;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Supplier;
use Carbon\Carbon;

class SyncService
{
    /**
     * @return array<string, mixed>
     */
    public function export(?Carbon $since = null): array
    {
        $products = Product::query()
            ->with(['unit', 'branchSettings', 'batches'])
            ->when($since, fn ($query) => $query->where('updated_at', '>=', $since))
            ->get();

        $promotions = Promotion::query()
            ->with('products:id,sku,name')
            ->when($since, fn ($query) => $query->where('updated_at', '>=', $since))
            ->get();

        $suppliers = Supplier::query()
            ->when($since, fn ($query) => $query->where('updated_at', '>=', $since))
            ->get();

        return [
            'generated_at' => now()->toIso8601String(),
            'products' => $products,
            'promotions' => $promotions,
            'suppliers' => $suppliers,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function import(array $payload): void
    {
        IntegrationLog::create([
            'channel' => 'offline.sync',
            'status' => 'queued',
            'payload' => $payload,
        ]);
    }
}
