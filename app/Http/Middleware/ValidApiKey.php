<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\InvalidApiKey;

use App\Models\ApiKey;

class ValidApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-API-KEY') !== null) {
            $isValidApiKey = ApiKey::where('token', $request->header('X-API-KEY'))->get();

            if ($isValidApiKey !== null) {
                return $next($request);
            }
        }

        throw new InvalidApiKey();
    }
}
