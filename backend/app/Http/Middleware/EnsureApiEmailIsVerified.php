<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->email_verified_at) {
            return response()->json([
                'message' => 'Please verify your email before using PesoTracker.',
                'code' => 'email_not_verified',
                'email' => $request->user()?->email,
            ], 403);
        }

        return $next($request);
    }
}
