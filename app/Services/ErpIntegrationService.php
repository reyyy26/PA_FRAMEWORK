<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Arr;

class ErpIntegrationService
{
    public function queueSalesExport(array $payload): void
    {
        IntegrationLog::create([
            'channel' => 'erp.sales',
            'status' => 'queued',
            'payload' => $payload,
        ]);
    }

    public function queueInventoryExport(array $payload): void
    {
        IntegrationLog::create([
            'channel' => 'erp.inventory',
            'status' => 'queued',
            'payload' => $payload,
        ]);
    }

    public function configure(array $settings): array
    {
        IntegrationLog::create([
            'channel' => 'erp.configure',
            'status' => 'queued',
            'payload' => $settings,
        ]);

        return Arr::only($settings, ['endpoint', 'api_key', 'mode']);
    }
}
