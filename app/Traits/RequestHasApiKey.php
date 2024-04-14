<?php

namespace App\Traits;

use App\Models\User;
use App\Models\ApiKey;

use App\Exceptions\InvalidApiKey;

trait RequestHasApiKey 
{
    /**
     * Retrieve the user from the 'X-API-KEY' request header
     * 
     * @param $reques
     * @return User
     */
    private function getUserFromApiKeyHeader ($request): User
    {
        $apiKey = ApiKey::where('token', $request->header('X-API-KEY'))->first();

        if (!$apiKey || !$apiKey->user) {
            throw new InvalidApiKey(); // verify that this is correct
        }
        
        return $apiKey->user;
    }
}