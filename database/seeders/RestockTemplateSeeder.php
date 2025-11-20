<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\RestockTemplateItem;
use Illuminate\Database\Seeder;

class RestockTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all()->keyBy('sku');

        $definitions = [
            ['sku' => 'PRD-001', 'quantity' => 40, 'sort' => 1],
            ['sku' => 'PRD-002', 'quantity' => 30, 'sort' => 2],
            ['sku' => 'PRD-003', 'quantity' => 20, 'sort' => 3],
            ['sku' => 'PRD-006', 'quantity' => 25, 'sort' => 4],
        ];

        foreach ($definitions as $definition) {
            $product = $products->get($definition['sku']);

            if (!$product) {
                continue;
            }

            RestockTemplateItem::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'default_quantity' => $definition['quantity'],
                    'sort_order' => $definition['sort'],
                    'is_active' => true,
                ]
            );
        }
    }
}
