<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryAdjustmentController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        $query = InventoryAdjustment::query()
            ->with(['product', 'branch'])
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
            'product_id' => ['required', 'exists:products,id'],
            'product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'reason' => ['required', 'string', 'max:255'],
            'quantity_change' => ['required', 'integer'],
            'note' => ['nullable', 'string'],
        ]);

        $adjustment = $this->inventory->recordAdjustment($data, $this->actorId($request));

        return response()->json($adjustment, 201);
    }

    private function actorId(Request $request): int
    {
        $actorId = $request->user()?->id ?? $request->integer('actor_id');
        abort_unless($actorId, 422, 'actor_id is required when user session is absent.');

        return $actorId;
    }
}
