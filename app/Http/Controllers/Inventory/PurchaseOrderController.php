<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        $query = PurchaseOrder::query()
            ->with([
                'supplier:id,name',
                'branch:id,name',
                'items.product:id,name'
            ])
            ->latest();

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
            'number' => ['nullable', 'string', 'max:100'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'expected_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'actor_id' => ['nullable', 'exists:users,id'],
        ]);

        $purchaseOrder = $this->inventory->createPurchaseOrder($data, $this->actorId($request));

        return response()->json($purchaseOrder, 201);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.unit_cost' => ['nullable', 'numeric'],
            'actor_id' => ['nullable', 'exists:users,id'],
        ]);

        $purchaseOrder = $this->inventory->receivePurchaseOrder($purchaseOrder, $data['items'], $this->actorId($request));

        return response()->json($purchaseOrder);
    }

    private function actorId(Request $request): int
    {
        $actorId = $request->user()?->id ?? $request->integer('actor_id');
        abort_unless($actorId, 422, 'actor_id is required when user session is absent.');

        return $actorId;
    }
}
