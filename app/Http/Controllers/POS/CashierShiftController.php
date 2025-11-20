<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Services\PosService;
use Illuminate\Http\Request;

class CashierShiftController extends Controller
{
    public function __construct(private readonly PosService $pos)
    {
    }

    public function index(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        $query = CashierShift::query()
            ->with('cashier:id,name')
            ->latest('opened_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json($query->get());
    }

    public function open(Request $request)
    {
        if (!$request->filled('branch_id') && $request->attributes->get('branch_id')) {
            $request->merge(['branch_id' => $request->attributes->get('branch_id')]);
        }

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'opening_float' => ['required', 'numeric', 'min:0'],
        ]);

        $shift = $this->pos->openShift($data['branch_id'], $this->actorId($request), $data['opening_float']);

        return response()->json($shift, 201);
    }

    public function close(Request $request, CashierShift $cashierShift)
    {
        $data = $request->validate([
            'closing_amount' => ['nullable', 'numeric'],
            'closing_notes' => ['nullable', 'array'],
        ]);

        $shift = $this->pos->closeShift($cashierShift, $data);

        return response()->json($shift);
    }

    private function actorId(Request $request): int
    {
        $actorId = $request->user()?->id ?? $request->integer('actor_id');
        abort_unless($actorId, 422, 'actor_id is required when user session is absent.');

        return $actorId;
    }
}
