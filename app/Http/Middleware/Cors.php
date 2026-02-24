<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // Pre-handle OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }
        
        // Always add the CORS headers regardless of origin for now (for testing)
        $response->header('Access-Control-Allow-Origin', $request->header('Origin'));
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization');
        $response->header('Access-Control-Allow-Credentials', 'true'); // This must be exactly 'true'
        $response->header('Access-Control-Max-Age', '86400');
        
        return $response;
    }
}