<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\IntegrationLog;
use App\Models\PosSale;
use App\Models\PurchaseOrder;
use App\Models\StockTransfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IntegrationAndAuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $integrationLogs = [
            ['channel' => 'seed.analytics', 'status' => 'queued', 'payload' => ['type' => 'daily_digest', 'sent_at' => Carbon::now()->toIso8601String()]],
            ['channel' => 'seed.erp', 'status' => 'success', 'payload' => ['endpoint' => 'https://erp-demo.nyxx.id', 'mode' => 'sync']],
            ['channel' => 'seed.notifications', 'status' => 'queued', 'payload' => ['medium' => 'slack', 'message' => 'Reorder suggestion ready']],
        ];

        foreach ($integrationLogs as $log) {
            IntegrationLog::create($log);
        }

        $actor = User::where('email', 'admin@demo.test')->first();

        $references = [
            ['action' => 'seed.purchase_order.received', 'model_type' => PurchaseOrder::class, 'model_id' => PurchaseOrder::where('number', 'PO-SEED-1001')->value('id')],
            ['action' => 'seed.stock_transfer.dispatched', 'model_type' => StockTransfer::class, 'model_id' => StockTransfer::where('number', 'ST-SEED-7001')->value('id')],
            ['action' => 'seed.pos_sale.completed', 'model_type' => PosSale::class, 'model_id' => PosSale::where('number', 'POS-SEED-1001')->value('id')],
        ];

        foreach ($references as $log) {
            if (!$log['model_id']) {
                continue;
            }

            AuditLog::create([
                'user_id' => $actor?->id,
                'action' => $log['action'],
                'model_type' => $log['model_type'],
                'model_id' => $log['model_id'],
                'changes' => ['seeded' => true],
            ]);
        }
    }
}
