<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        return response()->json(Promotion::with('products')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promotions,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed,bundle'],
            'value' => ['nullable', 'numeric'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'conditions' => ['nullable', 'array'],
            'product_ids' => ['nullable', 'array'],
        ]);

        $productIds = $data['product_ids'] ?? [];
        unset($data['product_ids']);

        $promotion = Promotion::create($data);
        $promotion->products()->sync($productIds);

        return response()->json($promotion->load('products'), 201);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:percentage,fixed,bundle'],
            'value' => ['nullable', 'numeric'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'conditions' => ['nullable', 'array'],
            'product_ids' => ['nullable', 'array'],
        ]);

        $productIds = $data['product_ids'] ?? null;
        unset($data['product_ids']);

        $promotion->update($data);

        if ($productIds !== null) {
            $promotion->products()->sync($productIds);
        }

        return response()->json($promotion->load('products'));
    }
}
