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
    protected $cardCreated = false;
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

        if (!$card) {
            $card = $this->createCardForTest($user);
            $this->cardCreated = true;
        }

        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->token,
        ])->post('/api/accounts/charge', [
            'amount' => $chargeAmount,
            'card_id' => $card->id
        ]);

        $response->assertStatus(200)->assertJson([
            'amount' => $expected_update,
         ]);

         Event::assertDispatched(AccountActivity::class);

         $this->deleteTestCard($card);
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

        if (!$card) {
            $card = $this->createCardForTest($user);
            $this->cardCreated = true;
        }

        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->token,
        ])->post('/api/accounts/debit', [
            'amount' => $debitAmount,
            'card_id' => $card->id
        ]);

        $response->assertStatus(200)->assertJson([
            'amount' => $expected_update,
         ]);
         
         Event::assertDispatched(AccountActivity::class);

         $this->deleteTestCard($card);
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

        if (!$card) {
            $card = $this->createCardForTest($user);
            $this->cardCreated = true;
        }

        $response = $this->withoutExceptionHandling()->withHeaders([
            'X-API-KEY' => $apiKey->token,
        ])->post('/api/accounts/debit', [
            'amount' => $debitAmount,
            'card_id' => $card->id
        ]);
      
        $this->deleteTestCard($card);
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

    private function createCardForTest($user) {
       return \App\Models\Card::factory()->create([
            'user_id' => $user->id,
            'number' => fake()->creditCardNumber(),
            'name' => $user->name,
            'exp' => fake()->creditCardExpirationDateString(),
            'cvc' => '000'
        ]);
    }

    private function deleteTestCard ($card)
    {
        if ($this->cardCreated === true) {
            $card->delete();
            $this->cardCreated = false;
        }
    }
}
