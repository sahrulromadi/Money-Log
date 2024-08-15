<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'amount' => fake()->numberBetween(1000, 100000),
            'description' => fake()->sentence(2),
            'transaction_date' => fake()->dateTimeThisYear(),
            'category_id' => Category::inRandomOrder()->value('id')
        ];
    }
}
