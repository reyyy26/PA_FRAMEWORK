<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        $query = StockTransfer::query()
            ->with([
                'items.product:id,name',
                'sourceBranch:id,name',
                'destinationBranch:id,name',
                'stockRequest'
            ])
            ->latest();

        if ($branchId) {
            $query->where(function ($inner) use ($branchId) {
                $inner->where('source_branch_id', $branchId)
                    ->orWhere('destination_branch_id', $branchId);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        if (!$request->filled('source_branch_id') && $request->attributes->get('branch_id')) {
            $request->merge(['source_branch_id' => $request->attributes->get('branch_id')]);
        }

        $data = $request->validate([
            'source_branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'exists:branches,id'],
            'stock_request_id' => ['nullable', 'exists:stock_requests,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'items.*.quantity_sent' => ['required', 'integer', 'min:1'],
        ]);

        $transfer = $this->inventory->createStockTransfer($data, $this->actorId($request));

        return response()->json($transfer, 201);
    }

    public function dispatch(Request $request, StockTransfer $stockTransfer)
    {
        $stockTransfer = $this->inventory->dispatchStockTransfer($stockTransfer->load('items'), $this->actorId($request));

        return response()->json($stockTransfer);
    }

    public function receive(Request $request, StockTransfer $stockTransfer)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
            'items.*.status' => ['nullable', 'string'],
            'items.*.product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'items.*.batch_number' => ['nullable', 'string'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.unit_cost' => ['nullable', 'numeric'],
        ]);

        $stockTransfer = $this->inventory->receiveStockTransfer($stockTransfer, $data['items'], $this->actorId($request));

        return response()->json($stockTransfer);
    }

    private function actorId(Request $request): int
    {
        $actorId = $request->user()?->id ?? $request->integer('actor_id');
        abort_unless($actorId, 422, 'actor_id is required when user session is absent.');

        return $actorId;
    }
}
