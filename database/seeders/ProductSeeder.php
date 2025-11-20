<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Liter', 'symbol' => 'L'],
            ['name' => 'Dus', 'symbol' => 'dus'],
            ['name' => 'Unit', 'symbol' => 'unit'],
        ];

        foreach ($units as $data) {
            ProductUnit::updateOrCreate(['name' => $data['name']], $data);
        }

        $unitMap = ProductUnit::all()->keyBy('name');

        $products = [
            [
                'sku' => 'PRD-001',
                'name' => 'Pupuk NPK 16-16-16',
                'unit' => 'Kilogram',
                'category' => 'Pupuk',
                'default_cost' => 120000,
                'default_price' => 155000,
                'track_batch' => true,
            ],
            [
                'sku' => 'PRD-002',
                'name' => 'Herbisida Kontak A',
                'unit' => 'Liter',
                'category' => 'Pestisida',
                'default_cost' => 95000,
                'default_price' => 132000,
                'track_batch' => true,
            ],
            [
                'sku' => 'PRD-003',
                'name' => 'Benih Jagung Hibrida',
                'unit' => 'Dus',
                'category' => 'Benih',
                'default_cost' => 210000,
                'default_price' => 260000,
                'track_batch' => true,
            ],
            [
                'sku' => 'PRD-004',
                'name' => 'Alat Semprot Elektrik',
                'unit' => 'Unit',
                'category' => 'Peralatan',
                'default_cost' => 480000,
                'default_price' => 625000,
                'track_batch' => false,
            ],
            [
                'sku' => 'PRD-005',
                'name' => 'Mulsa Plastik Hitam Perak',
                'unit' => 'Dus',
                'category' => 'Perlengkapan',
                'default_cost' => 185000,
                'default_price' => 230000,
                'track_batch' => true,
            ],
            [
                'sku' => 'PRD-006',
                'name' => 'Vitamin Tanaman Cair',
                'unit' => 'Liter',
                'category' => 'Nutrisi',
                'default_cost' => 67500,
                'default_price' => 99000,
                'track_batch' => true,
            ],
        ];

        foreach ($products as $data) {
            $unit = $unitMap->get($data['unit']);

            if (!$unit) {
                continue;
            }

            Product::updateOrCreate([
                'sku' => $data['sku'],
            ], [
                'name' => $data['name'],
                'product_unit_id' => $unit->id,
                'category' => $data['category'],
                'default_cost' => $data['default_cost'],
                'default_price' => $data['default_price'],
                'track_batch' => $data['track_batch'],
            ]);
        }
    }
}
