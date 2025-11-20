<?php

namespace App\Http\Controllers;

use App\Models\IntegrationLog;
use App\Services\ErpIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function __construct(private readonly ErpIntegrationService $erp)
    {
    }

    public function configure(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url'],
            'api_key' => ['required', 'string'],
            'mode' => ['required', 'in:sync,async'],
            'options' => ['nullable', 'array'],
        ]);

        return response()->json($this->erp->configure($data));
    }

    public function pushSales(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sales' => ['required', 'array'],
        ]);

        $this->erp->queueSalesExport($data);

        return response()->json(['status' => 'queued']);
    }

    public function pushInventory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'inventory' => ['required', 'array'],
        ]);

        $this->erp->queueInventoryExport($data);

        return response()->json(['status' => 'queued']);
    }

    public function logs(Request $request): JsonResponse
    {
        $query = IntegrationLog::query()->orderByDesc('created_at');

        if ($channel = $request->query('channel')) {
            $query->where('channel', $channel);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = min(max($request->integer('per_page', 25), 1), 100);
        $logs = $query->paginate($perPage);

        $logs->getCollection()->transform(function (IntegrationLog $log) {
            $payload = $log->payload;

            if (is_array($payload) && array_key_exists('api_key', $payload)) {
                $payload['api_key'] = '********';
                $log->setAttribute('payload', $payload);
            }

            return $log;
        });

        return response()->json($logs);
    }
}
