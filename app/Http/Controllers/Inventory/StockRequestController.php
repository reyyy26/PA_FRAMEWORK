<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockRequest;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockRequestController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        $query = StockRequest::query()
            ->with([
                'items.product:id,name',
                'requestingBranch:id,name',
                'targetBranch:id,name'
            ])
            ->latest();

        if ($branchId) {
            $query->where(function ($inner) use ($branchId) {
                $inner->where('requested_by_branch_id', $branchId)
                    ->orWhere('target_branch_id', $branchId);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        if (!$request->filled('requested_by_branch_id') && $request->attributes->get('branch_id')) {
            $request->merge(['requested_by_branch_id' => $request->attributes->get('branch_id')]);
        }

        $data = $request->validate([
            'requested_by_branch_id' => ['required', 'exists:branches,id'],
            'target_branch_id' => ['required', 'exists:branches,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_requested' => ['required', 'integer', 'min:1'],
        ]);

        $requestModel = $this->inventory->createStockRequest($data, $this->actorId($request));

        return response()->json($requestModel, 201);
    }

    public function approve(Request $request, StockRequest $stockRequest)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_approved' => ['required', 'integer', 'min:0'],
        ]);

        $stockRequest = $this->inventory->approveStockRequest($stockRequest, $data['items'], $this->actorId($request));

        return response()->json($stockRequest);
    }

    private function actorId(Request $request): int
    {
        $actorId = $request->user()?->id ?? $request->integer('actor_id');
        abort_unless($actorId, 422, 'actor_id is required when user session is absent.');

        return $actorId;
    }
}
