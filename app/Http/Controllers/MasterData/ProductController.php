<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBranchSetting;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        $query = Product::query()->with([
            'unit',
            'branchSettings' => function ($settings) use ($branchId) {
                if ($branchId) {
                    $settings->where('branch_id', $branchId);
                }
            },
            'batches' => function ($batches) use ($branchId) {
                if ($branchId) {
                    $batches->where('branch_id', $branchId);
                }
            },
        ]);

        if ($branchId) {
            $query->where(function ($inner) use ($branchId) {
                $inner->whereHas('branchSettings', fn ($q) => $q->where('branch_id', $branchId))
                    ->orWhereHas('batches', fn ($q) => $q->where('branch_id', $branchId));
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'product_unit_id' => ['required', 'exists:product_units,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'default_cost' => ['nullable', 'numeric'],
            'default_price' => ['nullable', 'numeric'],
            'track_batch' => ['sometimes', 'boolean'],
            'branch_settings' => ['nullable', 'array'],
        ]);

        $branchSettings = $data['branch_settings'] ?? [];
        unset($data['branch_settings']);

        $product = Product::create($data);

        foreach ($branchSettings as $setting) {
            ProductBranchSetting::updateOrCreate([
                'product_id' => $product->id,
                'branch_id' => $setting['branch_id'],
            ], [
                'reorder_point' => $setting['reorder_point'] ?? 0,
                'reorder_qty' => $setting['reorder_qty'] ?? 0,
                'selling_price' => $setting['selling_price'] ?? null,
            ]);
        }

        return response()->json($product->load(['unit', 'branchSettings']), 201);
    }

    public function show(Request $request, Product $product)
    {
        $branchId = $request->attributes->get('branch_id') ?? $request->integer('branch_id');

        return response()->json(
            $product->load([
                'unit',
                'branchSettings' => function ($settings) use ($branchId) {
                    if ($branchId) {
                        $settings->where('branch_id', $branchId);
                    }
                },
                'batches' => function ($batches) use ($branchId) {
                    if ($branchId) {
                        $batches->where('branch_id', $branchId);
                    }
                },
            ])
        );
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'product_unit_id' => ['sometimes', 'exists:product_units,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'default_cost' => ['nullable', 'numeric'],
            'default_price' => ['nullable', 'numeric'],
            'track_batch' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'branch_settings' => ['nullable', 'array'],
        ]);

        $branchSettings = $data['branch_settings'] ?? null;
        unset($data['branch_settings']);

        $product->update($data);

        if ($branchSettings !== null) {
            foreach ($branchSettings as $setting) {
                ProductBranchSetting::updateOrCreate([
                    'product_id' => $product->id,
                    'branch_id' => $setting['branch_id'],
                ], [
                    'reorder_point' => $setting['reorder_point'] ?? 0,
                    'reorder_qty' => $setting['reorder_qty'] ?? 0,
                    'selling_price' => $setting['selling_price'] ?? null,
                ]);
            }
        }

        return response()->json($product->load(['unit', 'branchSettings']));
    }
}
