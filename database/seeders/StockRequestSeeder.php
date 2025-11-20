<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockRequestSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');
        $users = User::all()->keyBy('email');
        $products = Product::all()->keyBy('sku');

        $requests = [
            [
                'number' => 'SR-SEED-9001',
                'requested_by' => 'CAB-01',
                'target' => 'MAIN-01',
                'status' => 'approved',
                'requester' => 'manager.bandung@demo.test',
                'approver' => 'procurement@demo.test',
                'items' => [
                    ['sku' => 'PRD-001', 'requested' => 40, 'approved' => 30],
                    ['sku' => 'PRD-002', 'requested' => 25, 'approved' => 20],
                ],
            ],
            [
                'number' => 'SR-SEED-9002',
                'requested_by' => 'CAB-02',
                'target' => 'MAIN-01',
                'status' => 'pending',
                'requester' => 'manager.surabaya@demo.test',
                'approver' => null,
                'items' => [
                    ['sku' => 'PRD-003', 'requested' => 18, 'approved' => 0],
                    ['sku' => 'PRD-006', 'requested' => 30, 'approved' => 0],
                ],
            ],
        ];

        foreach ($requests as $data) {
            $requestedBranch = $branches->get($data['requested_by']);
            $targetBranch = $branches->get($data['target']);
            $requester = $users->get($data['requester']);
            $approver = $data['approver'] ? $users->get($data['approver']) : null;

            if (!$requestedBranch || !$targetBranch || !$requester) {
                continue;
            }

            $stockRequest = StockRequest::updateOrCreate(
                ['number' => $data['number']],
                [
                    'requested_by_branch_id' => $requestedBranch->id,
                    'target_branch_id' => $targetBranch->id,
                    'status' => $data['status'],
                    'requested_by_user_id' => $requester->id,
                    'approved_by_user_id' => $approver?->id,
                ]
            );

            foreach ($data['items'] as $itemData) {
                $product = $products->get($itemData['sku']);

                if (!$product) {
                    continue;
                }

                StockRequestItem::updateOrCreate([
                    'stock_request_id' => $stockRequest->id,
                    'product_id' => $product->id,
                ], [
                    'quantity_requested' => $itemData['requested'],
                    'quantity_approved' => $itemData['approved'],
                ]);
            }
        }
    }
}
