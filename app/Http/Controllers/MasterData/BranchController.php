<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query()->with('settings');

        if ($user = $request->user()) {
            $branchIds = $user->branches()->pluck('branches.id')->all();
            if (!empty($branchIds)) {
                $query->whereIn('id', $branchIds);
            }
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:main,branch'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
        ]);

        $branch = Branch::create($data);

        return response()->json($branch, 201);
    }

    public function show(Branch $branch)
    {
        return response()->json($branch->load('settings'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:main,branch'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $branch->update($data);

        return response()->json($branch->refresh());
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return response()->noContent();
    }
}
