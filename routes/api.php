<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Middleware\ValidApiKey;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Strictly for getting information in testing
if (env('APP_ENV') !== 'production') {
    Route::get('/deps', function () {
        $user = \App\Models\User::inRandomOrder()->first();
        $card = $user->cards->first();
        $key = $user->apiKeys->first();

        return response()->json((object) [
            'user_id' => $user->id,
            'card_id' => $card->id,
            'apiKey' => $key->token
        ]);
    });
}

Route::middleware(ValidApiKey::class)->prefix('accounts')->controller(AccountController::class)->group(function () {
    Route::post('/charge', 'charge');
    Route::post('/debit', 'debit');
    Route::post('/withdraw', 'withdraw');

    // In the real world this would be protected via middleware and/or roles
    // These API routes are mainly for retrieving information for testing in tools such as PostMan
    Route::get('/cards/{user}', 'cards');
    Route::get('/keys/{user}', 'keys');
    Route::get('/', 'activity');
});
