<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchSetting;
use Illuminate\Http\Request;

class BranchSettingController extends Controller
{
    public function index(Branch $branch)
    {
        return response()->json($branch->settings);
    }

    public function store(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'value' => ['nullable'],
        ]);

        $setting = BranchSetting::updateOrCreate([
            'branch_id' => $branch->id,
            'key' => $data['key'],
        ], [
            'value' => $data['value'],
        ]);

        return response()->json($setting, 201);
    }
}
