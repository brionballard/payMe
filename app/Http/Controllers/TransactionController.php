<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Carbon\Carbon;

use App\Traits\RequestHasApiKey;

use App\Models\User;
use App\Models\Balance;
use App\Models\Transaction;

use App\Http\Requests\TransactionRequest;

use App\Exceptions\InvalidAmountValue;
use App\Exceptions\NonSufficientFunds;

use App\Events\TransactionCreated;

class TransactionController extends Controller
{
    use RequestHasApiKey;

    /**
     * Hanlde transactions based on request endpoint
     * 
     * @param TransactionRequest
     * @return JsonResponse
     */
    public function transact (TransactionRequest $request): JsonResponse
    {
        $timestamp = Carbon::now();
        $action = $request->action;
        
        $user = $this->getUserFromApiKeyHeader($request);
        $balance = $user->balance;

        switch (strtolower($action)) {
            case 'charge':
            case 'withdraw':
                $this->handleDecrementingAction($balance, $request->amount);
                break;

                case 'debit':
                case 'deposit':
                case 'refund':
                    $this->handleIncrementingAction($balance, $request->amount);
                    break;
            
            default:
                return response()->json('Transaction type not available', 400);
                break;
        }

        $activity = $this->createTransaction($user, $request, $action, $timestamp);
        $this->dispatchActivityEvent($activity);

        return response()->json($activity, 200);
        
    }

    /**
     * Handle decrementing balance if request amount is valid.
     * 
     * @param Balance
     * @param float
     * @return Balance || NonSufficientFunds
     */
    private function handleDecrementingAction (Balance $balance, $amount): mixed
    {
        if ($balance->amount <= 0 || $amount > $balance->amount) {
            throw new NonSufficientFunds();
        } else {
            $amount = abs($amount) * -1;
            return $this->updateBalance($balance, $amount); // convert amount to negative number
        }
    }

    /**
     * Handle incrementing balance if request amount is valid.
     * 
     * @param Balance
     * @param float
     * @return Balance || InvalidAmountValue
     */
    private function handleIncrementingAction (Balance $balance, $amount): mixed
    {
        if ($amount <= 0) {
            throw new InvalidAmountValue();
        } else {
            return $this->updateBalance($balance, $amount);
        }
    }

    /**
     * Upadate balance
     * * Always use addition to update the balance. 
     * * Expects $amount to be signed i.e. -10 + 1 = 9
     * @param Balance
     * @param float -- signed
     * @return Balance
     */
    private function updateBalance (Balance $balance, float $amount): Balance
    {
        $balance->amount = round($amount + $balance->amount, 2);
        $balance->save();
        return $balance;
    }

    /**
     * Format data for Transaction record and log event
     * @param User
     * @param TransactionRequest
     * @param string
     * @param Carbon
     * @return Transaction
     */
    private function createTransaction(User $user, TransactionRequest $request, string $action, Carbon $timestamp): Transaction
    {
        return Transaction::create([
            'user_id' => $user->id,
            'balance_after_activity' => $user->balance->amount,
            'amount' => floatval($request->amount),
            'timestamp' => $timestamp,
            'api_key' => $request->header('X-API-KEY'),
            'action' => $action
        ]);
    }

    /**
     * Dispatch activity event with formatted activity statement
     * @param Transaction
     */
    private function dispatchActivityEvent(Transaction $activity): void
    {
        TransactionCreated::dispatch($activity->formatLogFileStatement());
    }
}
