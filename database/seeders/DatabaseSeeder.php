<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\IntegrationLog;
use App\Models\CashierShift;
use App\Models\InventoryMovement;
use App\Models\PosSale;
use App\Models\PurchaseOrder;
use App\Models\RestockTemplateItem;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::transaction(function () {
            $this->resetSeededRecords();

            $this->call([
                BranchSeeder::class,
                UserSeeder::class,
                SupplierSeeder::class,
                CustomerSeeder::class,
                ProductSeeder::class,
                ProductBranchSettingSeeder::class,
                ProductBatchSeeder::class,
                RestockTemplateSeeder::class,
                PurchaseOrderSeeder::class,
                StockRequestSeeder::class,
                StockTransferSeeder::class,
                InventoryMovementSeeder::class,
                PosSaleSeeder::class,
                CashierShiftSeeder::class,
                PromotionSeeder::class,
                IntegrationAndAuditLogSeeder::class,
            ]);
        });
    }

    private function resetSeededRecords(): void
    {
        InventoryMovement::where('reference_type', 'seed')->delete();
        PosSale::where('number', 'like', 'POS-SEED-%')->each(fn (PosSale $sale) => $sale->delete());
        PurchaseOrder::where('number', 'like', 'PO-SEED-%')->each(fn (PurchaseOrder $order) => $order->delete());
        StockRequest::where('number', 'like', 'SR-SEED-%')->each(fn (StockRequest $request) => $request->delete());
        StockTransfer::where('number', 'like', 'ST-SEED-%')->each(fn (StockTransfer $transfer) => $transfer->delete());
        RestockTemplateItem::query()->delete();
        IntegrationLog::where('channel', 'like', 'seed.%')->delete();
        AuditLog::where('action', 'like', 'seed.%')->delete();
        CashierShift::where('closing_notes->source', 'seed')->delete();
    }
}
