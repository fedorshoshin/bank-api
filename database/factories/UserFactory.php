<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{


    public function definition(): array
    {
        return [
            'balance' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 500, $max = 100000),
        ];
    }

}
