<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchContext
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $branchId = $request->input('branch_id') ?? $request->session()->get('branch_id');

        if ($branchId && Branch::whereKey($branchId)->exists()) {
            $request->attributes->set('branch_id', $branchId);

            return $next($request);
        }

        abort(Response::HTTP_BAD_REQUEST, 'Branch context is required');
    }
}
