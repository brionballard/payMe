<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Traits\RequestHasApiKey;

class UserController extends Controller
{
    use RequestHasApiKey;

    /**
     * Return user transactions
     * @param Request
     * @return JsonResponse
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $this->getUserFromApiKeyHeader($request);

        return response()->json($user->transactions);
    }

    /**
     * Retrieve a user's balance
     * @param Request
     * @return JsonResponse
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $this->getUserFromApiKeyHeader($request);

        return response()->json($user->balance->amount);
    }

    /**
     * Retrieve a user's API Keys
     * @param Request
     * @return JsonResponse
     */
    public function keys(Request $request): JsonResponse
    {
        $user = $this->getUserFromApiKeyHeader($request);

        return response()->json($user->apiKeys);
    }
}
