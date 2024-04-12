<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class NotSufficientFunds extends Exception
{
     /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => 'NSF: Insufficient account balance.'
        ], 400);
    }
}
