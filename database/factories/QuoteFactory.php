<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'reference' => 'Q' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'converted']),
            'user_id' => User::factory(),
            'valid_until' => $this->faker->dateTimeBetween('now', '+30 days')
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending'
            ];
        });
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved'
            ];
        });
    }

    public function successful()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved'
            ];
        });
    }
}