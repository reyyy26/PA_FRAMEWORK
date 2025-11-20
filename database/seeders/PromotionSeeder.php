<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all()->keyBy('sku');

        $promotions = [
            [
                'code' => 'PROMO-PANEN',
                'name' => 'Diskon Musim Panen',
                'type' => 'percentage',
                'value' => 10,
                'starts_at' => Carbon::now()->subDays(5),
                'ends_at' => Carbon::now()->addDays(25),
                'is_active' => true,
                'conditions' => ['min_qty' => 5],
                'skus' => ['PRD-001', 'PRD-002'],
            ],
            [
                'code' => 'PROMO-ALAT',
                'name' => 'Bundling Peralatan',
                'type' => 'fixed',
                'value' => 50000,
                'starts_at' => Carbon::now()->subDays(10),
                'ends_at' => Carbon::now()->addDays(10),
                'is_active' => true,
                'conditions' => ['bundle' => ['PRD-004', 'PRD-005']],
                'skus' => ['PRD-004', 'PRD-005'],
            ],
        ];

        foreach ($promotions as $data) {
            $promotion = Promotion::updateOrCreate([
                'code' => $data['code'],
            ], [
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'is_active' => $data['is_active'],
                'conditions' => $data['conditions'],
            ]);

            $productIds = collect($data['skus'])
                ->map(fn ($sku) => $products->get($sku)?->id)
                ->filter()
                ->values()
                ->all();

            if (Schema::hasTable('product_promotion')) {
                $promotion->products()->sync($productIds);
            }
        }
    }
}
