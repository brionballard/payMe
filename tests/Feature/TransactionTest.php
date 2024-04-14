<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use App\Events\TransactionCreated;
use App\Models\ApiKey;

class TransactionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_charge(): void
    {
        Event::fake();

        $key = $this->getRandomApiKey();

        $charge = -20;
        $user = $key->user;
        $currentBalance = $user->balance;
        $expected_update = $charge + $currentBalance->amount;

        $response = $this->withHeaders([
            'X-API-KEY' => $key->token
        ])->post('/api/transactions/charge', [
            'amount' => $charge
        ]);

        $response->assertStatus(200)->assertJson([
            'balance_after_activity' => floatval($expected_update),
            'amount' => floatval($charge)
         ]);

         Event::assertDispatched(TransactionCreated::class);
    }

     /**
     * Test debiting a balance
     */
    public function test_debit_balance(): void
    {
        Event::fake();

        $debitAmount = 20;
        $key = $this->getRandomApiKey(); // move to own test case
        $user = $key->user;
        $currentBalance = $user->balance;
        $expected_update = $debitAmount + $currentBalance->amount;

        $response = $this->withHeaders([
            'X-API-KEY' => $key->token,
        ])->post('/api/transactions/debit', [
            'amount' => $debitAmount,
        ])->assertStatus(200);

        $response->assertJson([
            'balance_after_activity' => floatval($expected_update),
            'amount' => floatval($debitAmount)
         ]);
         
         Event::assertDispatched(TransactionCreated::class);
    }

    /**
     * Test debiting a balance with a negative amount
     */
    public function test_debit_balance_with_negative_amount(): void
    {
        $debitAmount = -20;
        $key = $this->getRandomApiKey(); // move to own test case
        $user = $key->user;
        $currentBalance = $user->balance;
        $expected_update = $debitAmount + $currentBalance->amount;
        
        $this->expectException(\App\Exceptions\InvalidAmountValue::class);

        $response = $this->withoutExceptionHandling()->withHeaders([
            'X-API-KEY' => $key->token,
        ])->post('/api/transactions/debit', [
            'amount' => floatval($debitAmount)
        ]);
    }

     /**
     * Retrieve a specific api key for request headers
     */
    private function getRandomApiKey (): ApiKey
    {
        $key = ApiKey::inRandomOrder()->first();

        return $key;
    }
}
