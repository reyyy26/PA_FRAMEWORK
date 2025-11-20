<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\PosPayment;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosService
{
    public function __construct(
        protected InventoryService $inventory,
        protected AuditService $audit,
        protected NotificationService $notifier,
    ) {
    }

    public function openShift(int $branchId, int $cashierId, float $openingFloat): CashierShift
    {
        $shift = CashierShift::create([
            'branch_id' => $branchId,
            'cashier_id' => $cashierId,
            'opening_float' => $openingFloat,
            'opened_at' => now(),
        ]);

        $this->audit->log('cashier_shift.opened', $shift);

        return $shift;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function closeShift(CashierShift $shift, array $data): CashierShift
    {
        $shift->update([
            'closing_amount' => $data['closing_amount'] ?? null,
            'closing_notes' => $data['closing_notes'] ?? null,
            'closed_at' => now(),
        ]);

        $this->audit->log('cashier_shift.closed', $shift, $data);

        $this->notifier->notify('cashier_shift.closed', [
            'shift_id' => $shift->id,
            'branch_id' => $shift->branch_id,
        ]);

        return $shift->fresh();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function recordSale(array $data, int $cashierId): PosSale
    {
        return DB::transaction(function () use ($data, $cashierId) {
            $items = collect($data['items'] ?? [])
                ->map(fn ($item) => array_merge($item, [
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                ]));

            $subtotal = $items->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

            $promotion = null;
            $discount = 0;

            if (!empty($data['promotion_id'])) {
                $promotion = Promotion::find($data['promotion_id']);
                if ($promotion && $promotion->is_active) {
                    $discount = $this->calculateDiscount($promotion, $subtotal);
                }
            }

            if ($discount > $subtotal) {
                $discount = $subtotal;
            }

            $customerId = null;
            if (!empty($data['customer'])) {
                $customerPayload = $data['customer'];
                $customerPayload['is_opt_in'] = (bool) ($customerPayload['is_opt_in'] ?? false);
                $customer = Customer::create($customerPayload);
                $customerId = $customer->id;
            }

            /** @var PosSale $sale */
            $sale = PosSale::create([
                'number' => $data['number'] ?? Str::ulid(),
                'branch_id' => $data['branch_id'],
                'cashier_id' => $cashierId,
                'customer_id' => $customerId,
                'promotion_id' => $promotion?->id,
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'tax_total' => $data['tax_total'] ?? 0,
                'grand_total' => $subtotal - $discount + ($data['tax_total'] ?? 0),
                'status' => 'completed',
                'sold_at' => $data['sold_at'] ?? now(),
            ]);

            $items->each(function ($item) use ($sale, $cashierId) {
                $saleItem = PosSaleItem::create([
                    'pos_sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_batch_id' => $item['product_batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0),
                ]);

                if (!empty($item['product_batch_id'])) {
                    ProductBatch::find($item['product_batch_id'])?->decrement('quantity', $saleItem->quantity);
                }

                InventoryMovement::create([
                    'product_id' => $saleItem->product_id,
                    'branch_id' => $sale->branch_id,
                    'product_batch_id' => $saleItem->product_batch_id,
                    'type' => 'sale',
                    'quantity' => -$saleItem->quantity,
                    'unit_cost' => optional($saleItem->batch)->cost_price ?? 0,
                    'reference_type' => PosSale::class,
                    'reference_id' => $sale->id,
                    'performed_by' => $cashierId,
                    'performed_at' => now(),
                ]);
            });

            foreach ($data['payments'] ?? [] as $payment) {
                PosPayment::create([
                    'pos_sale_id' => $sale->id,
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'meta' => $payment['meta'] ?? null,
                ]);
            }

            $this->audit->log('pos.sale_recorded', $sale);

            $this->notifier->notify('pos.sale_recorded', [
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'grand_total' => $sale->grand_total,
            ]);

            return $sale->load(['items', 'payments']);
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function quickSale(array $data, int $cashierId): PosSale
    {
        $items = collect($data['items'] ?? [])->map(function ($item) use ($data) {
            $product = Product::findOrFail($item['product_id']);
            $quantity = (int) $item['quantity'];

            if ($quantity <= 0) {
                abort(422, 'Quantity must be greater than zero');
            }

            $unitPrice = (float) ($item['unit_price'] ?? $product->default_price ?? 0);

            if ($unitPrice < 0) {
                abort(422, 'Unit price cannot be negative');
            }

            $discount = (float) ($item['discount'] ?? 0);
            $lineTotal = $quantity * $unitPrice;

            if ($discount < 0 || $discount > $lineTotal) {
                abort(422, 'Discount must be between 0 and line total');
            }

            if (!empty($item['product_batch_id'])) {
                ProductBatch::query()
                    ->where('product_id', $product->id)
                    ->where('branch_id', $data['branch_id'])
                    ->findOrFail($item['product_batch_id']);
            }

            return [
                'product_id' => $product->id,
                'product_batch_id' => $item['product_batch_id'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
            ];
        });

        $taxTotal = (float) ($data['tax_total'] ?? 0);

        $payments = collect($data['payments'] ?? [])->map(function ($payment) {
            return [
                'method' => $payment['method'],
                'amount' => (float) $payment['amount'],
                'meta' => $payment['meta'] ?? null,
            ];
        });

        if ($payments->isEmpty()) {
            $subtotal = $items->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
            $discountTotal = $items->sum(fn ($item) => $item['discount'] ?? 0);
            $grandTotal = max(0, $subtotal - $discountTotal + $taxTotal);

            $payments = collect([[
                'method' => $data['payment_method'] ?? 'cash',
                'amount' => $grandTotal,
                'meta' => null,
            ]]);
        }

        $payload = [
            'number' => $data['number'] ?? null,
            'branch_id' => $data['branch_id'],
            'sold_at' => $data['sold_at'] ?? now(),
            'items' => $items->toArray(),
            'payments' => $payments->toArray(),
            'tax_total' => $taxTotal,
        ];

        if (!empty($data['customer'])) {
            $payload['customer'] = $data['customer'];
        }

        return $this->recordSale($payload, $cashierId);
    }

    protected function calculateDiscount(Promotion $promotion, float $subtotal): float
    {
        return match ($promotion->type) {
            'percentage' => $subtotal * ($promotion->value / 100),
            'fixed' => $promotion->value,
            default => 0,
        };
    }
}
