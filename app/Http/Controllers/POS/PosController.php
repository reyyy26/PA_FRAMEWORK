<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Product;
use App\Models\PosSale;
use App\Models\StockTransfer;
use App\Services\PosService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(private readonly PosService $pos)
    {
    }

    public function index(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        $query = PosSale::query()
            ->with([
                'items.product:id,name',
                'payments',
                'cashier:id,name'
            ])
            ->latest('sold_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sold_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sold_at', '<=', $request->date('date_to'));
        }

        $limit = (int) $request->query('limit', 50);
        $limit = max(10, min(200, $limit));

        return response()->json($query->take($limit)->get());
    }

    public function sale(Request $request)
    {
        if (!$request->filled('branch_id') && $request->attributes->get('branch_id')) {
            $request->merge(['branch_id' => $request->attributes->get('branch_id')]);
        }

        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['required', 'exists:branches,id'],
            'sold_at' => ['nullable', 'date'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'string'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.meta' => ['nullable', 'array'],
            'customer' => ['nullable', 'array'],
            'customer.name' => ['required_with:customer', 'string'],
            'customer.phone' => ['nullable', 'string'],
            'customer.email' => ['nullable', 'email'],
            'customer.is_opt_in' => ['nullable', 'boolean'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
        ]);

        $sale = $this->pos->recordSale($data, $this->actorId($request));

        return response()->json($sale, 201);
    }

    public function products(Request $request)
    {
        $search = $request->query('q');

        $query = Product::query()
            ->select('id', 'sku', 'name', 'default_price')
            ->where('is_active', true)
            ->orderBy('name');

        if ($search) {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return response()->json($query->limit(50)->get());
    }

    public function quickSale(Request $request)
    {
        if (!$request->filled('branch_id') && $request->attributes->get('branch_id')) {
            $request->merge(['branch_id' => $request->attributes->get('branch_id')]);
        }

        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['required', 'exists:branches,id'],
            'sold_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.method' => ['required_with:payments', 'string'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
            'payments.*.meta' => ['nullable', 'array'],
            'payment_method' => ['nullable', 'string'],
            'customer' => ['nullable', 'array'],
            'customer.name' => ['nullable', 'string'],
            'customer.phone' => ['nullable', 'string'],
            'customer.email' => ['nullable', 'email'],
            'customer.is_opt_in' => ['nullable', 'boolean'],
        ]);

        $data['branch_id'] = $request->attributes->get('branch_id') ?? $data['branch_id'];

        $sale = $this->pos->quickSale($data, $this->actorId($request));

        return response()->json($sale, 201);
    }

    public function overview(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');
        $date = $request->date('date', now());

        if (!$branchId) {
            return response()->json([
                'summary' => [
                    'transactions' => 0,
                    'grand_total' => 0,
                    'cash_total' => 0,
                    'non_cash_total' => 0,
                    'payments' => [],
                ],
                'active_shift' => null,
                'recent_shifts' => [],
                'inbound_transfers' => 0,
            ]);
        }

        $sales = PosSale::query()
            ->with('payments')
            ->where('branch_id', $branchId)
            ->whereDate('sold_at', $date)
            ->get();

        $paymentsByMethod = [];
        foreach ($sales as $sale) {
            foreach ($sale->payments as $payment) {
                $method = strtolower((string) $payment->method ?: 'other');
                $amount = (float) $payment->amount;
                $paymentsByMethod[$method] = ($paymentsByMethod[$method] ?? 0) + $amount;
            }
        }

        $cashTotal = $paymentsByMethod['cash'] ?? 0;
        $nonCashTotal = collect($paymentsByMethod)
            ->except(['cash'])
            ->sum();

        $summary = [
            'transactions' => $sales->count(),
            'grand_total' => (float) $sales->sum(fn ($sale) => (float) $sale->grand_total),
            'cash_total' => $cashTotal,
            'non_cash_total' => $nonCashTotal,
            'payments' => $paymentsByMethod,
        ];

        $shifts = CashierShift::query()
            ->with('branch:id,name')
            ->where('branch_id', $branchId)
            ->orderByDesc('opened_at')
            ->limit(5)
            ->get();

        $userId = $request->user()?->id;

        $activeShift = $shifts->first(function ($shift) use ($userId) {
            return !$shift->closed_at && (!$userId || $shift->cashier_id === $userId);
        }) ?? $shifts->firstWhere('closed_at', null);

        $inboundTransfers = StockTransfer::query()
            ->where('destination_branch_id', $branchId)
            ->where('status', 'in_transit')
            ->count();

        return response()->json([
            'summary' => $summary,
            'active_shift' => $activeShift,
            'recent_shifts' => $shifts->values(),
            'inbound_transfers' => $inboundTransfers,
        ]);
    }

    private function actorId(Request $request): int
    {
        $actorId = $request->user()?->id ?? $request->integer('actor_id');
        abort_unless($actorId, 422, 'actor_id is required when user session is absent.');

        return $actorId;
    }
}
