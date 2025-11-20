<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InventoryMovementSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');
        $products = Product::all()->keyBy('sku');
        $batches = ProductBatch::all()->keyBy('batch_number');
        $users = User::all()->keyBy('email');

        $actor = $users->get('procurement@demo.test') ?? $users->first();

        $movements = [
            ['type' => 'purchase', 'sku' => 'PRD-001', 'branch' => 'MAIN-01', 'batch' => 'MAIN-PRD-001-A', 'qty' => 150, 'unit_cost' => 118000, 'when' => Carbon::now()->subDays(3)],
            ['type' => 'purchase', 'sku' => 'PRD-002', 'branch' => 'MAIN-01', 'batch' => 'MAIN-PRD-002-A', 'qty' => 120, 'unit_cost' => 93000, 'when' => Carbon::now()->subDays(35)],
            ['type' => 'sale', 'sku' => 'PRD-001', 'branch' => 'CAB-01', 'batch' => 'CAB1-PRD-001-A', 'qty' => 35, 'unit_cost' => 150000, 'when' => Carbon::now()->subDays(5)],
            ['type' => 'sale', 'sku' => 'PRD-002', 'branch' => 'CAB-01', 'batch' => 'CAB1-PRD-002-A', 'qty' => 12, 'unit_cost' => 135000, 'when' => Carbon::now()->subDays(8)],
            ['type' => 'sale', 'sku' => 'PRD-006', 'branch' => 'CAB-02', 'batch' => 'CAB2-PRD-006-EXP', 'qty' => 6, 'unit_cost' => 99000, 'when' => Carbon::now()->subDays(12)],
            ['type' => 'transfer_out', 'sku' => 'PRD-001', 'branch' => 'MAIN-01', 'batch' => 'MAIN-PRD-001-A', 'qty' => 30, 'unit_cost' => 118000, 'when' => Carbon::now()->subDays(2)],
            ['type' => 'transfer_in', 'sku' => 'PRD-003', 'branch' => 'CAB-02', 'batch' => 'CAB2-PRD-003-A', 'qty' => 15, 'unit_cost' => 210000, 'when' => Carbon::now()->subDays(3)],
            ['type' => 'adjustment', 'sku' => 'PRD-002', 'branch' => 'MAIN-01', 'batch' => 'MAIN-PRD-002-A', 'qty' => -5, 'unit_cost' => 93000, 'when' => Carbon::now()->subDays(1)],
        ];

        foreach ($movements as $data) {
            $product = $products->get($data['sku']);
            $branch = $branches->get($data['branch']);
            $batch = $data['batch'] ? $batches->get($data['batch']) : null;

            if (!$product || !$branch) {
                continue;
            }

            $quantity = in_array($data['type'], ['sale', 'transfer_out'], true) || $data['qty'] < 0
                ? -abs($data['qty'])
                : abs($data['qty']);

            $referenceKey = sprintf('%s:%s:%s:%s', $data['type'], $branch->code, $product->sku, $data['batch'] ?? 'none');

            InventoryMovement::updateOrCreate([
                'reference_type' => 'seed',
                'reference_id' => $this->makeSeedReferenceId($referenceKey),
            ], [
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'product_batch_id' => $batch?->id,
                'type' => $data['type'],
                'quantity' => $quantity,
                'unit_cost' => $data['unit_cost'],
                'performed_by' => $actor?->id,
                'performed_at' => $data['when'],
            ]);
        }
    }

    private function makeSeedReferenceId(string $key): int
    {
        return (int) sprintf('%u', crc32($key));
    }
}
