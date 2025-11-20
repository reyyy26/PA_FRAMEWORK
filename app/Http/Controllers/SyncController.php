<?php

namespace App\Http\Controllers;

use App\Services\SyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function export(Request $request): JsonResponse
    {
        $since = $request->filled('since') ? Carbon::parse($request->input('since')) : null;

        return response()->json($this->sync->export($since));
    }

    public function import(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payload' => ['required', 'array'],
        ]);

        $this->sync->import($data['payload']);

        return response()->json(['status' => 'queued']);
    }
}
