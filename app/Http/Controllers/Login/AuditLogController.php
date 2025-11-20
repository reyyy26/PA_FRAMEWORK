<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()->with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        if ($request->filled('from')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->input('from'))->startOfDay(),
                Carbon::parse($request->input('to', now()))->endOfDay(),
            ]);
        }

        $logs = $query->latest()->paginate($request->integer('per_page', 50));

        return response()->json($logs);
    }
}
