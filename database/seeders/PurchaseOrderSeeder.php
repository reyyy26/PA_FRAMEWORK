<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');
        $suppliers = Supplier::all()->keyBy('code');
        $users = User::all()->keyBy('email');
        $products = Product::all()->keyBy('sku');

        $orders = [
            [
                'number' => 'PO-SEED-1001',
                'branch' => 'MAIN-01',
                'supplier' => 'SUP-001',
                'status' => 'received',
                'expected_date' => Carbon::now()->subDays(15),
                'created_by' => 'procurement@demo.test',
                'approved_by' => 'admin@demo.test',
                'items' => [
                    ['sku' => 'PRD-001', 'ordered' => 150, 'received' => 150, 'unit_cost' => 118000],
                    ['sku' => 'PRD-002', 'ordered' => 120, 'received' => 120, 'unit_cost' => 93000],
                ],
            ],
            [
                'number' => 'PO-SEED-1002',
                'branch' => 'CAB-01',
                'supplier' => 'SUP-002',
                'status' => 'approved',
                'expected_date' => Carbon::now()->addDays(7),
                'created_by' => 'manager.bandung@demo.test',
                'approved_by' => 'procurement@demo.test',
                'items' => [
                    ['sku' => 'PRD-003', 'ordered' => 40, 'received' => 0, 'unit_cost' => 208000],
                    ['sku' => 'PRD-006', 'ordered' => 80, 'received' => 0, 'unit_cost' => 68000],
                ],
            ],
            [
                'number' => 'PO-SEED-1003',
                'branch' => 'CAB-02',
                'supplier' => 'SUP-003',
                'status' => 'processing',
                'expected_date' => Carbon::now()->addDays(3),
                'created_by' => 'manager.surabaya@demo.test',
                'approved_by' => 'procurement@demo.test',
                'items' => [
                    ['sku' => 'PRD-005', 'ordered' => 60, 'received' => 0, 'unit_cost' => 182000],
                    ['sku' => 'PRD-002', 'ordered' => 50, 'received' => 0, 'unit_cost' => 94000],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $branch = $branches->get($orderData['branch']);
            $supplier = $suppliers->get($orderData['supplier']);
            $creator = $users->get($orderData['created_by']);
            $approver = $orderData['approved_by'] ? $users->get($orderData['approved_by']) : null;

            if (!$branch || !$supplier || !$creator) {
                continue;
            }

            $totalCost = collect($orderData['items'])
                ->sum(fn ($item) => $item['unit_cost'] * $item['ordered']);

            $purchaseOrder = PurchaseOrder::updateOrCreate(
                ['number' => $orderData['number']],
                [
                    'branch_id' => $branch->id,
                    'supplier_id' => $supplier->id,
                    'status' => $orderData['status'],
                    'expected_date' => $orderData['expected_date'],
                    'total_cost' => $totalCost,
                    'created_by' => $creator->id,
                    'approved_by' => $approver?->id,
                ]
            );

            foreach ($orderData['items'] as $itemData) {
                $product = $products->get($itemData['sku']);

                if (!$product) {
                    continue;
                }

                PurchaseOrderItem::updateOrCreate([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                ], [
                    'quantity_ordered' => $itemData['ordered'],
                    'quantity_received' => $itemData['received'],
                    'unit_cost' => $itemData['unit_cost'],
                ]);
            }
        }
    }
}
