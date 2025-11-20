<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\PosPayment;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PosSaleSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');
        $products = Product::all()->keyBy('sku');
        $batches = ProductBatch::all()->keyBy('batch_number');
        $users = User::all()->keyBy('email');
        $customers = Customer::all()->keyBy('email');

        $sales = [
            [
                'number' => 'POS-SEED-1001',
                'branch' => 'CAB-01',
                'cashier' => 'kasir.bandung@demo.test',
                'customer' => 'rahmat@example.com',
                'sold_at' => Carbon::now()->subDays(2)->setTime(10, 15),
                'status' => 'completed',
                'tax' => 0,
                'items' => [
                    ['sku' => 'PRD-001', 'batch' => 'CAB1-PRD-001-A', 'quantity' => 3, 'unit_price' => 165000, 'discount' => 5000],
                    ['sku' => 'PRD-002', 'batch' => 'CAB1-PRD-002-A', 'quantity' => 2, 'unit_price' => 139000, 'discount' => 0],
                ],
                'payments' => [
                    ['method' => 'cash', 'amount' => 613000],
                ],
            ],
            [
                'number' => 'POS-SEED-1002',
                'branch' => 'CAB-02',
                'cashier' => 'kasir.surabaya@demo.test',
                'customer' => 'sari@example.com',
                'sold_at' => Carbon::now()->subDays(1)->setTime(14, 45),
                'status' => 'completed',
                'tax' => 12000,
                'items' => [
                    ['sku' => 'PRD-003', 'batch' => 'CAB2-PRD-003-A', 'quantity' => 2, 'unit_price' => 265000, 'discount' => 10000],
                    ['sku' => 'PRD-006', 'batch' => 'CAB2-PRD-006-EXP', 'quantity' => 1, 'unit_price' => 99000, 'discount' => 0],
                ],
                'payments' => [
                    ['method' => 'transfer', 'amount' => 619000, 'meta' => ['bank' => 'BCA', 'account' => '123-456789-0']],
                ],
            ],
            [
                'number' => 'POS-SEED-1003',
                'branch' => 'MAIN-01',
                'cashier' => 'procurement@demo.test',
                'customer' => 'pengadaan@lumbung.id',
                'sold_at' => Carbon::now()->subDays(6)->setTime(9, 5),
                'status' => 'completed',
                'tax' => 0,
                'items' => [
                    ['sku' => 'PRD-004', 'batch' => null, 'quantity' => 4, 'unit_price' => 630000, 'discount' => 20000],
                ],
                'payments' => [
                    ['method' => 'invoice', 'amount' => 2480000, 'meta' => ['due_date' => Carbon::now()->addDays(14)->toDateString()]],
                ],
            ],
            [
                'number' => 'POS-SEED-2001',
                'branch' => 'CAB-01',
                'cashier' => 'kasir.bandung@demo.test',
                'customer' => 'rahmat@example.com',
                'sold_at' => Carbon::today()->setTime(11, 30),
                'status' => 'completed',
                'tax' => 7500,
                'items' => [
                    ['sku' => 'PRD-001', 'batch' => 'CAB1-PRD-001-A', 'quantity' => 2, 'unit_price' => 165000, 'discount' => 5000],
                    ['sku' => 'PRD-002', 'batch' => 'CAB1-PRD-002-A', 'quantity' => 1, 'unit_price' => 139000, 'discount' => 0],
                ],
                'payments' => [
                    ['method' => 'cash', 'amount' => 300000],
                    ['method' => 'transfer', 'amount' => 171500, 'meta' => ['bank' => 'Mandiri', 'reference' => 'TRX-2001']],
                ],
            ],
            [
                'number' => 'POS-SEED-2002',
                'branch' => 'CAB-02',
                'cashier' => 'kasir.surabaya@demo.test',
                'customer' => 'sari@example.com',
                'sold_at' => Carbon::today()->setTime(15, 10),
                'status' => 'completed',
                'tax' => 5000,
                'items' => [
                    ['sku' => 'PRD-003', 'batch' => 'CAB2-PRD-003-A', 'quantity' => 1, 'unit_price' => 265000, 'discount' => 5000],
                    ['sku' => 'PRD-006', 'batch' => 'CAB2-PRD-006-EXP', 'quantity' => 2, 'unit_price' => 99000, 'discount' => 0],
                ],
                'payments' => [
                    ['method' => 'cash', 'amount' => 150000],
                    ['method' => 'ewallet', 'amount' => 313000, 'meta' => ['provider' => 'OVO']],
                ],
            ],
            [
                'number' => 'POS-SEED-2003',
                'branch' => 'MAIN-01',
                'cashier' => 'procurement@demo.test',
                'customer' => 'pengadaan@lumbung.id',
                'sold_at' => Carbon::today()->setTime(10, 5),
                'status' => 'completed',
                'tax' => 12000,
                'items' => [
                    ['sku' => 'PRD-004', 'batch' => null, 'quantity' => 1, 'unit_price' => 630000, 'discount' => 0],
                    ['sku' => 'PRD-005', 'batch' => null, 'quantity' => 2, 'unit_price' => 210000, 'discount' => 10000],
                ],
                'payments' => [
                    ['method' => 'invoice', 'amount' => 1052000, 'meta' => ['due_date' => Carbon::today()->addDays(7)->toDateString()]],
                ],
            ],
        ];

        foreach ($sales as $data) {
            $branch = $branches->get($data['branch']);
            $cashier = $users->get($data['cashier']);
            $customer = $customers->get($data['customer']);

            if (!$branch || !$cashier) {
                continue;
            }

            $items = collect($data['items'])
                ->map(function ($item) use ($products, $batches) {
                    $product = $products->get($item['sku']);

                    if (!$product) {
                        return null;
                    }

                    return [
                        'product' => $product,
                        'batch' => $item['batch'] ? $batches->get($item['batch']) : null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'],
                        'total' => ($item['quantity'] * $item['unit_price']) - $item['discount'],
                    ];
                })
                ->filter();

            if ($items->isEmpty()) {
                continue;
            }

            $subtotal = $items->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
            $discountTotal = $items->sum(fn ($item) => $item['discount']);
            $grandTotal = $subtotal - $discountTotal + $data['tax'];

            $sale = PosSale::updateOrCreate(
                ['number' => $data['number']],
                [
                    'branch_id' => $branch->id,
                    'cashier_id' => $cashier->id,
                    'customer_id' => $customer?->id,
                    'promotion_id' => null,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'tax_total' => $data['tax'],
                    'grand_total' => $grandTotal,
                    'status' => $data['status'],
                    'sold_at' => $data['sold_at'],
                ]
            );

            foreach ($items as $item) {
                PosSaleItem::updateOrCreate([
                    'pos_sale_id' => $sale->id,
                    'product_id' => $item['product']->id,
                ], [
                    'product_batch_id' => $item['batch']?->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'],
                    'total' => $item['total'],
                ]);
            }

            foreach ($data['payments'] as $payment) {
                PosPayment::updateOrCreate([
                    'pos_sale_id' => $sale->id,
                    'method' => $payment['method'],
                ], [
                    'amount' => $payment['amount'],
                    'meta' => $payment['meta'] ?? null,
                ]);
            }
        }
    }
}
