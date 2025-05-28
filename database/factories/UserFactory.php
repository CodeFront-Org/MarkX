<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role' => $this->faker->randomElement(['client', 'marketer', 'manager']),
            'phone' => $this->faker->numerify('##########'),
            'location' => $this->faker->city(),
            'about_me' => $this->faker->sentence(),
        ];
    }

    /**
     * Set the user as a marketer.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function marketer()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'marketer',
            ];
        });
    }

}
