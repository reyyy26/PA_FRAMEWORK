<?php

namespace App\Http\Controllers;

use App\Services\AutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function __construct(private readonly AutomationService $automation)
    {
    }

    public function reorder(Request $request): JsonResponse
    {
        $suggestions = $this->automation->evaluateReorder((bool) $request->boolean('notify', true));

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }
}
