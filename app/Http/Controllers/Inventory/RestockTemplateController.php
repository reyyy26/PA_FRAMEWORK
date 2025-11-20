<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RestockTemplateItem;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RestockTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $items = $this->templateItemsQuery()->get();

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'default_quantity' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $productId = $data['product_id'];

        abort_if(
            RestockTemplateItem::where('product_id', $productId)->exists(),
            422,
            'Produk sudah ada dalam daftar restok.'
        );

        $item = RestockTemplateItem::create([
            'product_id' => $productId,
            'default_quantity' => $data['default_quantity'] ?? 1,
            'sort_order' => $data['sort_order'] ?? (RestockTemplateItem::max('sort_order') + 1),
        ]);

        return response()->json($item->load('product.unit'), 201);
    }

    public function update(Request $request, RestockTemplateItem $restockTemplateItem): JsonResponse
    {
        $data = $request->validate([
            'default_quantity' => ['sometimes', 'integer', 'min:1'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $restockTemplateItem->update($data);

        return response()->json($restockTemplateItem->refresh()->load('product.unit'));
    }

    public function destroy(RestockTemplateItem $restockTemplateItem): Response
    {
        $restockTemplateItem->delete();

        return response()->noContent();
    }

    public function options(): JsonResponse
    {
        return response()->json($this->activeTemplateItems());
    }

    public function products(Request $request): JsonResponse
    {
        $search = $request->query('q');

        $query = Product::query()
            ->select('id', 'sku', 'name')
            ->where('is_active', true)
            ->orderBy('name');

        if ($search) {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return response()->json($query->limit(25)->get());
    }

    public function bootstrap(): JsonResponse
    {
        return response()->json([
            'template_items' => $this->activeTemplateItems(),
            'suppliers' => Supplier::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    protected function templateItemsQuery(): Builder
    {
        return RestockTemplateItem::with(['product.unit'])
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    protected function activeTemplateItems(): Collection
    {
        return $this->templateItemsQuery()
            ->where('is_active', true)
            ->get()
            ->filter(fn ($item) => $item->product !== null && $item->product->is_active)
            ->values();
    }
}
