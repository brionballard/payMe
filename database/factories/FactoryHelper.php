<?php

namespace Database\Factories;

use App\Models\User;

trait FactoryHelper
{
    protected $not_in;

    /**
     * Get a random user
     * @param null | []int - Array of user ids
     * @return User
     */
    public function getRandomUser(array $not_in): User
    {
        $users = User::get();

        if (count($not_in) >= 1) {
            $users = User::whereNotIn('id', $not_in)->get();
        }

        $min = $users[0]->id;
        $max = count($users) - 1;

        return $users[random_int($min, $max)];
    }
}