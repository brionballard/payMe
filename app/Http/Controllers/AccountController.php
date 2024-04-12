<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Balance;
use App\Models\ActivityLog;

use App\Http\Requests\ChangeBalanceRequest;

use App\Exceptions\InvalidAmountValue;
use App\Exceptions\InvalidApiKey;
use App\Exceptions\NonSufficientFunds;

use App\Events\AccountActivity;

class AccountController extends Controller
{
    /**
     * Return user account activity
     * @param Request
     * @return JsonResponse
     */
    public function activity(Request $request): JsonResponse
    {
        $user = $this->getUserFromApiKey($request);

        return response()->json($user->activity);
    }

    /**
     * Retrieve a user's cards
     * @param Request
     * @return JsonResponse
     */
    public function cards(Request $request, User $user): JsonResponse
    {
        return response()->json($user->cards);
    }

    /**
     * Retrieve a user's API Keys
     * @param Request
     * @return JsonResponse
     */
    public function keys(Request $request, User $user): JsonResponse
    {
        return response()->json($user->apiKeys);
    }

    /**
     * Charge a balance
     * 
     * * Can accept signed or unsigned amounts
     * * Charges can be taken from account even if account balance is zero
     * * Once processed, fires an event to log the details
     * @param ChangeBalanceRequest
     * @return JsonResponse
     */
    public function charge(ChangeBalanceRequest $request): JsonResponse
    {
        $timestamp = \Carbon\Carbon::now();

        $data = $this->processRequestData($request);
        $shouldSubtract = true; // flag to handle signed amount values
        
        if ($data->requestAmount < 0) {
            $shouldSubtract = false; // if signed number should be added to value
        }

        $updatedAmount = $shouldSubtract ? 
            $data->balance->amount - $data->requestAmount : 
            $data->balance->amount + $data->requestAmount;
    
        $balance = $this->handleBalanceUpdate($data->balance, $updatedAmount);
        $activityData = $this->formatActivityData($data, $balance);
        $activityData['action'] = 'charged';
        
        $this->handleNewActivity($activityData);

        return response()->json($activityData, 200);
    }

    /**
     * Debit a balance if request amount is greater than 0.
     * 
     * * Once processed, fires an event to log the details
     * @param ChangeBalanceRequest
     * @return JsonResponse
     */
    public function debit(ChangeBalanceRequest $request)
    {
        $timestamp = \Carbon\Carbon::now();
        $data = $this->processRequestData($request);
        
        if ($data->requestAmount < 0) {
            throw new InvalidAmountValue();
        }

        $updatedAmount = $data->balance->amount + $data->requestAmount;
        $balance = $this->handleBalanceUpdate($data->balance, $updatedAmount);
        $activityData = $this->formatActivityData($data, $balance);
        $activityData['action'] = 'debited';
        
        $this->handleNewActivity($activityData);
        
        return response()->json($activityData, 200);
    }

    /**
     * If balance is sufficient withdraw requested amount.
     * 
     * * Once processed, fires an event to log the details
     * @param ChangeBalanceRequest
     * @return JsonResponse
     */
    public function withdraw(ChangeBalanceRequest $request)
    {
        $timestamp = \Carbon\Carbon::now();
        $data = $this->processRequestData($request);

        if ($data->balance->amount <= 0 || $data->requestAmount > $data->balance->amount) {
            throw new NonSufficientFunds();
        }

        if ($data->requestAmount < 0) {
            throw new InvalidAmountValue();
        }

        $updatedAmount = $data->balance->amount - $data->requestAmount;
        $balance = $this->handleBalanceUpdate($data->balance, $updatedAmount);
        $activityData = $this->formatActivityData($data, $balance);
        $activityData['action'] = 'withdrew';
        
        $this->handleNewActivity($activityData);

        // Explicitly set available amount from withdrawals after activity is stored
        // ** primarily for client side consumption
        $activityData['available'] = $data->requestAmount;
        
        return response()->json($activityData, 200);
    }

    /**
     * Destructure request for account
     * @param Request | ChangeBalanceRequest
     * @return object
     */
    private function processRequestData ($request): object
    {
        $user = $this->getUserFromApiKey($request);
        $apiKey = $request->header('X-API-KEY');

        return (object) [
            'user' => $user,
            'balance' => $user->balance,
            'card_id' => $request->card_id,
            'requestAmount' => $request->amount,
            'apiKey' => $apiKey
        ];
    }

    /**
     * Retrieve the user from the 'X-API-KEY' request header
     * 
     * @param $request - ChangeBalanceRequest or Request
     * @return User
     */
    private function getUserFromApiKey ($request): User
    {
        $apiKey = ApiKey::where('token', $request->header('X-API-KEY'))->first();

        if (!$apiKey || !$apiKey->user) {
            throw new InvalidApiKey(); // verify that this is correct
        }
        
        return $apiKey->user;
    }

    /**
     * Helper function to update balance amount
     * @param Balance
     * @param float
     * @return Balance
     */
    private function handleBalanceUpdate (Balance $balance, float $amount): Balance
    {
        $balance->update([
            'amount' => round($amount, 2)
        ]);

        return Balance::find($balance->id);
    }

    /**
     * Format data for ActivityLog record and log event
     * @param object - data generated from $this->processRequestData
     * @param Balance
     * @return array
     */
    private function formatActivityData(object $data, Balance $balance): array
    {
        return [
            'user_id' => $data->user->id,
            'balance_at_time_of_activity' => $balance->amount,
            'amount' => $data->requestAmount,
            'card_id' => $data->card_id,
            'timestamp' => $timestamp,
            'api_key' => $data->apiKey,
        ];
    }

    /**
     * Store new activity record, format string & dispatch activity logging
     * @param array
     */
    private function handleNewActivity(array $activityData): void
    {
        $newActivityStatement = ActivityLog::create($activityData)->formatLogFileStatement();
        AccountActivity::dispatch($newActivityStatement);
    }
}
