<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use App\Events\AccountActivity;
use App\Models\ApiKey;
use Exception;

class AccountTest extends TestCase
{
    /**
     * Test charging a balance
     */
    public function test_charging_balance(): void
    {
        Event::fake();

        $chargeAmount = -20;
        $apiKey = $this->getRandomApiKey(); // move to own test case
        $user = $apiKey->user;
        $card = $user->cards->first();
        $currentBalance = $user->balance;
        $expected_update = $chargeAmount + $currentBalance->amount;

        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->token,
        ])->post('/api/accounts/charge', [
            'amount' => $chargeAmount,
            'card_id' => $card->id
        ]);

        $response->assertStatus(200)->assertJson([
            'balance_at_time_of_activity' => floatval($expected_update),
            'amount' => floatval($chargeAmount)
         ]);

         Event::assertDispatched(AccountActivity::class);
    }

     /**
     * Test debiting a balance
     */
    public function test_debit_balance(): void
    {
        Event::fake();

        $debitAmount = 20;
        $apiKey = $this->getRandomApiKey(); // move to own test case
        $user = $apiKey->user;
        $card = $user->cards->first();
        $currentBalance = $user->balance;
        $expected_update = $debitAmount + $currentBalance->amount;

        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->token,
        ])->post('/api/accounts/debit', [
            'amount' => $debitAmount,
            'card_id' => $card->id
        ]);

        $response->assertStatus(200)->assertJson([
            'balance_at_time_of_activity' => floatval($expected_update),
            'amount' => floatval($debitAmount)
         ]);
         
         Event::assertDispatched(AccountActivity::class);
    }

    /**
     * Test debiting a balance with a negative amount
     */
    public function test_debit_balance_with_negative_amount(): void
    {
        $debitAmount = -20;
        $apiKey = $this->getRandomApiKey(); // move to own test case
        $user = $apiKey->user;
        $card = $user->cards->first();
        $currentBalance = $user->balance;
        $expected_update = $debitAmount + $currentBalance->amount;
        
        $this->expectException(\App\Exceptions\InvalidAmountValue::class);

        $response = $this->withoutExceptionHandling()->withHeaders([
            'X-API-KEY' => $apiKey->token,
        ])->post('/api/accounts/debit', [
            'amount' => $debitAmount,
            'card_id' => $card->id
        ]);
    }

    /**
     * Retrieve a specific api key for request headers
     */
    private function getRandomApiKey (): ApiKey
    {
        $keys = ApiKey::get();

        $min = $keys[0]->id;
        $max = count($keys) - 1;

        return $keys[random_int($min, $max)];
    }
}
