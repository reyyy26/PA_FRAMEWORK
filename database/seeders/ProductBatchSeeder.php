<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductBatchSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all()->keyBy('sku');
        $branches = Branch::all()->keyBy('code');

        $definitions = [
            ['sku' => 'PRD-001', 'branch' => 'MAIN-01', 'batch' => 'MAIN-PRD-001-A', 'expiry' => Carbon::now()->addMonths(6), 'cost' => 118000, 'qty' => 220],
            ['sku' => 'PRD-001', 'branch' => 'CAB-01', 'batch' => 'CAB1-PRD-001-A', 'expiry' => Carbon::now()->addDays(20), 'cost' => 125000, 'qty' => 75],
            ['sku' => 'PRD-001', 'branch' => 'CAB-02', 'batch' => 'CAB2-PRD-001-A', 'expiry' => Carbon::now()->addMonths(4), 'cost' => 126000, 'qty' => 90],
            ['sku' => 'PRD-002', 'branch' => 'MAIN-01', 'batch' => 'MAIN-PRD-002-A', 'expiry' => Carbon::now()->addMonths(2), 'cost' => 93000, 'qty' => 140],
            ['sku' => 'PRD-002', 'branch' => 'CAB-01', 'batch' => 'CAB1-PRD-002-A', 'expiry' => Carbon::now()->addDays(12), 'cost' => 95000, 'qty' => 40],
            ['sku' => 'PRD-003', 'branch' => 'MAIN-01', 'batch' => 'MAIN-PRD-003-A', 'expiry' => Carbon::now()->addMonths(12), 'cost' => 205000, 'qty' => 65],
            ['sku' => 'PRD-003', 'branch' => 'CAB-02', 'batch' => 'CAB2-PRD-003-A', 'expiry' => Carbon::now()->addMonths(10), 'cost' => 210000, 'qty' => 35],
            ['sku' => 'PRD-006', 'branch' => 'CAB-02', 'batch' => 'CAB2-PRD-006-EXP', 'expiry' => Carbon::now()->subDays(10), 'cost' => 66000, 'qty' => 28],
        ];

        foreach ($definitions as $data) {
            $product = $products->get($data['sku']);
            $branch = $branches->get($data['branch']);

            if (!$product || !$branch) {
                continue;
            }

            ProductBatch::updateOrCreate([
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'batch_number' => $data['batch'],
            ], [
                'expiry_date' => $data['expiry'],
                'cost_price' => $data['cost'],
                'quantity' => $data['qty'],
                'quantity_reserved' => 0,
            ]);
        }
    }
}
