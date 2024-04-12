<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->count(10)->create();


        $users = \App\Models\User::get();
        // handling balance creation indiviually to avoid multiple user balances
        foreach ($users as $user) { 
            \App\Models\Balance::create([
                'user_id' => $user->id,
                'amount' => fake()->randomFloat(2, 1, 3000)
            ]);

            \App\Models\Card::create([
                'user_id' => $user->id,
                'number' => fake()->creditCardNumber(),
                'name' => $user->name,
                'exp' => fake()->creditCardExpirationDateString(),
                'cvc' => '000'
            ]);

            \App\Models\ApiKey::create([
                'user_id' => $user->id,
                'token' => Str::random(16)
            ]);
        }
        
    }
}
