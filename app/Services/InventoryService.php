<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class InventoryService
{
    public function __construct(
        protected AuditService $audit,
        protected NotificationService $notifier,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createPurchaseOrder(array $data, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = collect($data['items'] ?? []);

            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder = PurchaseOrder::create([
                'number' => $data['number'] ?? Str::ulid(),
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'],
                'status' => $data['status'] ?? 'draft',
                'expected_date' => $data['expected_date'] ?? null,
                'total_cost' => $items->sum(fn ($item) => (float) ($item['unit_cost'] ?? 0) * (int) ($item['quantity_ordered'] ?? 0)),
                'created_by' => $userId,
            ]);

            $items->each(function ($item) use ($purchaseOrder) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => $item['quantity_received'] ?? 0,
                    'unit_cost' => $item['unit_cost'],
                ]);
            });

            $this->audit->log('purchase_order.created', $purchaseOrder, [
                'items' => $items->toArray(),
            ]);

            return $purchaseOrder->load('items');
        });
    }

    /**
     * @param array<int, array<string, mixed>> $receivedItems
     */
    public function receivePurchaseOrder(PurchaseOrder $purchaseOrder, array $receivedItems, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $receivedItems, $userId) {
            $purchaseOrder->update([
                'status' => 'received',
                'approved_by' => $userId,
            ]);

            foreach ($receivedItems as $itemData) {
                /** @var PurchaseOrderItem $item */
                $item = $purchaseOrder->items()->where('product_id', $itemData['product_id'])->firstOrFail();
                $quantity = (int) $itemData['quantity_received'];
                $batchNumber = $itemData['batch_number'] ?? Str::uuid()->toString();
                $batch = ProductBatch::updateOrCreate([
                    'product_id' => $item->product_id,
                    'branch_id' => $purchaseOrder->branch_id,
                    'batch_number' => $batchNumber,
                ], [
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'cost_price' => $itemData['unit_cost'] ?? $item->unit_cost,
                ]);

                $batch->increment('quantity', $quantity);
                $item->increment('quantity_received', $quantity);

                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'branch_id' => $purchaseOrder->branch_id,
                    'product_batch_id' => $batch->id,
                    'type' => 'purchase',
                    'quantity' => $quantity,
                    'unit_cost' => $itemData['unit_cost'] ?? $item->unit_cost,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $purchaseOrder->id,
                    'performed_by' => $userId,
                    'performed_at' => now(),
                ]);
            }

            $this->audit->log('purchase_order.received', $purchaseOrder, [
                'items' => $receivedItems,
            ]);

            $this->notifier->notify('purchase_order.received', [
                'purchase_order_id' => $purchaseOrder->id,
                'branch_id' => $purchaseOrder->branch_id,
            ]);

            return $purchaseOrder->fresh('items');
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createStockRequest(array $data, int $userId): StockRequest
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = collect($data['items'] ?? []);

            /** @var StockRequest $request */
            $request = StockRequest::create([
                'number' => $data['number'] ?? Str::ulid(),
                'requested_by_branch_id' => $data['requested_by_branch_id'],
                'target_branch_id' => $data['target_branch_id'],
                'status' => 'pending',
                'requested_by_user_id' => $userId,
            ]);

            $items->each(function ($item) use ($request) {
                StockRequestItem::create([
                    'stock_request_id' => $request->id,
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_approved' => $item['quantity_approved'] ?? 0,
                ]);
            });

            $this->audit->log('stock_request.created', $request, ['items' => $items->toArray()]);

            $this->notifier->notify('stock_request.created', [
                'stock_request_id' => $request->id,
                'target_branch_id' => $data['target_branch_id'],
            ]);

            return $request->load('items');
        });
    }

    public function approveStockRequest(StockRequest $request, array $items, int $userId): StockRequest
    {
        return DB::transaction(function () use ($request, $items, $userId) {
            foreach ($items as $item) {
                $request->items()->where('product_id', $item['product_id'])->update([
                    'quantity_approved' => Arr::get($item, 'quantity_approved', 0),
                ]);
            }

            $request->update([
                'status' => 'approved',
                'approved_by_user_id' => $userId,
            ]);

            $this->audit->log('stock_request.approved', $request, ['items' => $items]);

            return $request->fresh('items');
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createStockTransfer(array $data, int $userId): StockTransfer
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = collect($data['items'] ?? []);

            /** @var StockTransfer $transfer */
            $transfer = StockTransfer::create([
                'number' => $data['number'] ?? Str::ulid(),
                'source_branch_id' => $data['source_branch_id'],
                'destination_branch_id' => $data['destination_branch_id'],
                'stock_request_id' => $data['stock_request_id'] ?? null,
                'status' => 'preparing',
                'dispatched_by' => $userId,
            ]);

            $items->each(function ($item) use ($transfer) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'product_batch_id' => $item['product_batch_id'] ?? null,
                    'quantity_sent' => $item['quantity_sent'],
                    'quantity_received' => 0,
                    'status' => 'pending',
                ]);
            });

            $this->audit->log('stock_transfer.created', $transfer, ['items' => $items->toArray()]);

            return $transfer->load('items');
        });
    }

    public function dispatchStockTransfer(StockTransfer $transfer, int $userId): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            $transfer->loadMissing('items.batch');

            foreach ($transfer->items as $item) {
                $quantity = $item->quantity_sent;
                $batchId = $item->product_batch_id;

                if ($batchId) {
                    $batch = ProductBatch::findOrFail($batchId);
                    $batch->decrement('quantity', $quantity);
                }

                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'branch_id' => $transfer->source_branch_id,
                    'product_batch_id' => $batchId,
                    'type' => 'transfer_out',
                    'quantity' => -$quantity,
                    'unit_cost' => optional($item->batch)->cost_price ?? 0,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'performed_by' => $userId,
                    'performed_at' => now(),
                ]);
            }

            $transfer->update([
                'status' => 'in_transit',
                'dispatched_at' => now(),
                'dispatched_by' => $userId,
            ]);

            $this->audit->log('stock_transfer.dispatched', $transfer);

            return $transfer->fresh('items');
        });
    }

    public function receiveStockTransfer(StockTransfer $transfer, array $items, int $userId): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $items, $userId) {
            $transfer->loadMissing('items');

            foreach ($items as $itemData) {
                /** @var StockTransferItem $item */
                $item = $transfer->items()->where('product_id', $itemData['product_id'])->firstOrFail();
                $receivedQty = (int) $itemData['quantity_received'];
                $item->update([
                    'quantity_received' => $receivedQty,
                    'status' => $itemData['status'] ?? ($receivedQty === $item->quantity_sent ? 'received' : 'discrepancy'),
                ]);

                if (!empty($itemData['product_batch_id'])) {
                    $batch = ProductBatch::findOrFail($itemData['product_batch_id']);
                } else {
                    $batch = ProductBatch::create([
                        'product_id' => $item->product_id,
                        'branch_id' => $transfer->destination_branch_id,
                        'batch_number' => $itemData['batch_number'] ?? Str::uuid()->toString(),
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'cost_price' => $itemData['unit_cost'] ?? 0,
                        'quantity' => 0,
                        'quantity_reserved' => 0,
                    ]);
                }

                $batch->increment('quantity', $receivedQty);

                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'branch_id' => $transfer->destination_branch_id,
                    'product_batch_id' => $batch->id,
                    'type' => 'transfer_in',
                    'quantity' => $receivedQty,
                    'unit_cost' => $batch->cost_price ?? 0,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'performed_by' => $userId,
                    'performed_at' => now(),
                ]);
            }

            $transfer->load('items');
            $transfer->update([
                'status' => $transfer->items->contains(fn ($item) => $item->status === 'discrepancy') ? 'partially_delivered' : 'delivered',
                'delivered_at' => now(),
                'received_by' => $userId,
            ]);

            $this->audit->log('stock_transfer.received', $transfer, ['items' => $items]);

            $this->notifier->notify('stock_transfer.received', [
                'stock_transfer_id' => $transfer->id,
                'destination_branch_id' => $transfer->destination_branch_id,
            ]);

            return $transfer->fresh('items');
        });
    }

    public function recordAdjustment(array $data, int $userId): InventoryAdjustment
    {
        return DB::transaction(function () use ($data, $userId) {
            /** @var InventoryAdjustment $adjustment */
            $adjustment = InventoryAdjustment::create([
                'branch_id' => $data['branch_id'],
                'product_id' => $data['product_id'],
                'product_batch_id' => $data['product_batch_id'] ?? null,
                'reason' => $data['reason'],
                'quantity_change' => (int) $data['quantity_change'],
                'note' => $data['note'] ?? null,
                'performed_by' => $userId,
            ]);

            if ($adjustment->product_batch_id) {
                ProductBatch::find($adjustment->product_batch_id)?->increment('quantity', $adjustment->quantity_change);
            }

            InventoryMovement::create([
                'product_id' => $adjustment->product_id,
                'branch_id' => $adjustment->branch_id,
                'product_batch_id' => $adjustment->product_batch_id,
                'type' => 'adjustment',
                'quantity' => $adjustment->quantity_change,
                'unit_cost' => optional($adjustment->batch)->cost_price,
                'reference_type' => InventoryAdjustment::class,
                'reference_id' => $adjustment->id,
                'performed_by' => $userId,
                'performed_at' => now(),
            ]);

            $this->audit->log('inventory_adjustment.created', $adjustment);

            return $adjustment;
        });
    }

    public function startStockCount(array $data, int $userId): StockCount
    {
        return DB::transaction(function () use ($data, $userId) {
            /** @var StockCount $count */
            $count = StockCount::create([
                'number' => $data['number'] ?? Str::ulid(),
                'branch_id' => $data['branch_id'],
                'status' => 'in_progress',
                'requested_by' => $userId,
            ]);

            $items = collect($data['items'] ?? []);

            $items->each(function ($item) use ($count) {
                $systemQty = $this->currentQuantity($item['product_id'], $item['product_batch_id'] ?? null, $count->branch_id);

                StockCountItem::create([
                    'stock_count_id' => $count->id,
                    'product_id' => $item['product_id'],
                    'product_batch_id' => $item['product_batch_id'] ?? null,
                    'quantity_system' => $systemQty,
                    'quantity_counted' => $item['quantity_counted'] ?? 0,
                    'variance' => ($item['quantity_counted'] ?? 0) - $systemQty,
                ]);
            });

            $this->audit->log('stock_count.started', $count, ['items' => $items->toArray()]);

            return $count->load('items');
        });
    }

    public function closeStockCount(StockCount $count, int $userId): StockCount
    {
        return DB::transaction(function () use ($count, $userId) {
            $count->update([
                'status' => 'reviewed',
                'reviewed_by' => $userId,
            ]);

            foreach ($count->items as $item) {
                if ($item->variance === 0) {
                    continue;
                }

                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'branch_id' => $count->branch_id,
                    'product_batch_id' => $item->product_batch_id,
                    'type' => 'count',
                    'quantity' => $item->variance,
                    'unit_cost' => optional($item->batch)->cost_price,
                    'reference_type' => StockCount::class,
                    'reference_id' => $count->id,
                    'performed_by' => $userId,
                    'performed_at' => now(),
                ]);

                if ($item->product_batch_id) {
                    ProductBatch::find($item->product_batch_id)?->increment('quantity', $item->variance);
                }
            }

            $count->update(['status' => 'posted']);

            $this->audit->log('stock_count.closed', $count);

            return $count->fresh('items');
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function quickRestock(array $data, int $userId): InventoryMovement
    {
        return DB::transaction(function () use ($data, $userId) {
            $product = Product::findOrFail($data['product_id']);
            $branchId = (int) $data['branch_id'];
            $quantity = (int) $data['quantity'];
            $unitCost = (float) ($data['unit_cost'] ?? $product->default_cost ?? 0);

            if ($quantity <= 0) {
                abort(422, 'Quantity must be greater than zero');
            }

            if ($unitCost < 0) {
                abort(422, 'Unit cost cannot be negative');
            }

            if (!empty($data['product_batch_id'])) {
                $batch = ProductBatch::query()
                    ->where('product_id', $product->id)
                    ->where('branch_id', $branchId)
                    ->findOrFail($data['product_batch_id']);
            } else {
                $batchNumber = $data['batch_number'] ?? sprintf('%s-%s', Str::upper($product->sku ?? 'SKU'), now()->format('YmdHis'));

                $batch = ProductBatch::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'branch_id' => $branchId,
                        'batch_number' => $batchNumber,
                    ],
                    [
                        'expiry_date' => $data['expiry_date'] ?? null,
                        'cost_price' => $unitCost,
                        'quantity' => 0,
                        'quantity_reserved' => 0,
                    ]
                );

                $shouldUpdate = false;

                if (!empty($data['expiry_date'])) {
                    $batch->expiry_date = $data['expiry_date'];
                    $shouldUpdate = true;
                }

                if (array_key_exists('unit_cost', $data)) {
                    $batch->cost_price = $unitCost;
                    $shouldUpdate = true;
                } elseif ($batch->cost_price === null) {
                    $batch->cost_price = $unitCost;
                    $shouldUpdate = true;
                }

                if ($shouldUpdate) {
                    $batch->save();
                }
            }

            $batch->increment('quantity', $quantity);

            /** @var InventoryMovement $movement */
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'product_batch_id' => $batch->id,
                'type' => 'purchase',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'reference_type' => 'quick_restock',
                'reference_id' => null,
                'performed_by' => $userId,
                'performed_at' => now(),
            ]);

            $this->audit->log('inventory.quick_restock', $movement, [
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'batch_id' => $batch->id,
            ]);

            $this->notifier->notify('inventory.quick_restock', [
                'movement_id' => $movement->id,
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'quantity' => $quantity,
            ]);

            return $movement->load('batch');
        });
    }

    protected function currentQuantity(int $productId, ?int $batchId, int $branchId): int
    {
        $query = ProductBatch::where('product_id', $productId)
            ->where('branch_id', $branchId);

        if ($batchId) {
            $query->where('id', $batchId);
        }

        return (int) $query->sum('quantity');
    }
}
