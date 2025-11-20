<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockCount;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockCountController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        $query = StockCount::query()
            ->with(['items.product:id,name', 'branch:id,name'])
            ->latest('created_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        if (!$request->filled('branch_id') && $request->attributes->get('branch_id')) {
            $request->merge(['branch_id' => $request->attributes->get('branch_id')]);
        }

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'items.*.quantity_counted' => ['required', 'integer', 'min:0'],
        ]);

        $count = $this->inventory->startStockCount($data, $this->actorId($request));

        return response()->json($count, 201);
    }

    public function close(Request $request, StockCount $stockCount)
    {
        $stockCount = $this->inventory->closeStockCount($stockCount->load('items'), $this->actorId($request));

        return response()->json($stockCount);
    }

    private function actorId(Request $request): int
    {
        $actorId = $request->user()?->id ?? $request->integer('actor_id');
        abort_unless($actorId, 422, 'actor_id is required when user session is absent.');

        return $actorId;
    }
}
