<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiToken
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $abilities = null): Response
    {
        abort(Response::HTTP_UNAUTHORIZED, 'Token-based access has been disabled.');
    }

}
