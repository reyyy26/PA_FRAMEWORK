<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StockTransferSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');
        $products = Product::all()->keyBy('sku');
        $batches = ProductBatch::all()->keyBy('batch_number');
        $requests = StockRequest::all()->keyBy('number');
        $users = User::all()->keyBy('email');

        $transfers = [
            [
                'number' => 'ST-SEED-7001',
                'source' => 'MAIN-01',
                'destination' => 'CAB-01',
                'status' => 'in_transit',
                'stock_request' => 'SR-SEED-9001',
                'dispatched_by' => 'procurement@demo.test',
                'received_by' => null,
                'dispatched_at' => Carbon::now()->subDays(2),
                'delivered_at' => null,
                'items' => [
                    ['sku' => 'PRD-001', 'batch' => 'MAIN-PRD-001-A', 'sent' => 30, 'received' => 0, 'status' => 'pending'],
                    ['sku' => 'PRD-002', 'batch' => 'MAIN-PRD-002-A', 'sent' => 20, 'received' => 0, 'status' => 'pending'],
                ],
            ],
            [
                'number' => 'ST-SEED-7002',
                'source' => 'MAIN-01',
                'destination' => 'CAB-02',
                'status' => 'delivered',
                'stock_request' => null,
                'dispatched_by' => 'procurement@demo.test',
                'received_by' => 'manager.surabaya@demo.test',
                'dispatched_at' => Carbon::now()->subDays(5),
                'delivered_at' => Carbon::now()->subDays(3),
                'items' => [
                    ['sku' => 'PRD-003', 'batch' => 'MAIN-PRD-003-A', 'sent' => 15, 'received' => 15, 'status' => 'received'],
                ],
            ],
        ];

        foreach ($transfers as $data) {
            $source = $branches->get($data['source']);
            $destination = $branches->get($data['destination']);
            $dispatcher = $users->get($data['dispatched_by']);
            $receiver = $data['received_by'] ? $users->get($data['received_by']) : null;
            $linkedRequest = $data['stock_request'] ? $requests->get($data['stock_request']) : null;

            if (!$source || !$destination || !$dispatcher) {
                continue;
            }

            $transfer = StockTransfer::updateOrCreate(
                ['number' => $data['number']],
                [
                    'source_branch_id' => $source->id,
                    'destination_branch_id' => $destination->id,
                    'stock_request_id' => $linkedRequest?->id,
                    'status' => $data['status'],
                    'dispatched_by' => $dispatcher->id,
                    'received_by' => $receiver?->id,
                    'dispatched_at' => $data['dispatched_at'],
                    'delivered_at' => $data['delivered_at'],
                ]
            );

            foreach ($data['items'] as $itemData) {
                $product = $products->get($itemData['sku']);
                $batch = $itemData['batch'] ? $batches->get($itemData['batch']) : null;

                if (!$product) {
                    continue;
                }

                StockTransferItem::updateOrCreate([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                ], [
                    'product_batch_id' => $batch?->id,
                    'quantity_sent' => $itemData['sent'],
                    'quantity_received' => $itemData['received'],
                    'status' => $itemData['status'],
                ]);
            }
        }
    }
}
