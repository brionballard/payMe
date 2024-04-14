<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

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
// * This route should only be used to retrieve data to setup request in tools
// * such as Postman. This is not a "real" route.
if (env('APP_ENV') !== 'production') {
    Route::get('/issue-key', function () {
        $user = \App\Models\User::inRandomOrder()->first();
        return response()->json($user->apiKeys->first()->token);
    });
}


Route::middleware(ValidApiKey::class)->group(function () {
    Route::prefix('transactions')->controller(TransactionController::class)->group(function () {
        Route::post('/{action}', 'transact');
    });

    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/transactions', 'transactions');
        Route::get('/balance', 'balance');
        Route::get('/keys', 'keys');
    });
});

