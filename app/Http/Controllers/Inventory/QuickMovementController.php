<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuickMovementController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function restock(Request $request): JsonResponse
    {
        if (!$request->filled('branch_id') && $request->attributes->get('branch_id')) {
            $request->merge(['branch_id' => $request->attributes->get('branch_id')]);
        }

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'product_id' => ['required', 'exists:products,id'],
            'product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $movement = $this->inventory->quickRestock($data, $this->actorId($request));

        return response()->json(
            $movement
                ->loadMissing([
                    'product:id,name,sku',
                    'branch:id,name,code',
                    'batch:id,product_id,branch_id,batch_number,expiry_date,cost_price,quantity',
                ])
        );
    }

    private function actorId(Request $request): int
    {
        $actorId = $request->user()?->id ?? $request->integer('actor_id');
        abort_unless($actorId, 422, 'actor_id is required when user session is absent.');

        return $actorId;
    }
}
