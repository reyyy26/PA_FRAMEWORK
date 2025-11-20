<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchSetting;
use Illuminate\Database\Seeder;

class ProductBranchSettingSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all()->keyBy('sku');
        $branches = Branch::all()->keyBy('code');

        foreach ($products as $product) {
            foreach ($branches as $branch) {
                ProductBranchSetting::updateOrCreate([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                ], [
                    'reorder_point' => match ($branch->code) {
                        'MAIN-01' => 120,
                        'CAB-01' => 60,
                        'CAB-02' => 45,
                        default => 30,
                    },
                    'reorder_qty' => match ($product->sku) {
                        'PRD-004' => 15,
                        'PRD-005' => 40,
                        default => 90,
                    },
                    'selling_price' => $product->default_price,
                ]);
            }
        }
    }
}
