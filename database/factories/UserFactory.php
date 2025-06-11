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
            'role' => $this->faker->randomElement(['client', 'rfq_processor', 'rfq_approver', 'lpo_admin']),
            'phone' => $this->faker->numerify('##########'),
            'location' => $this->faker->city(),
            'about_me' => $this->faker->sentence(),
        ];
    }

    /**
     * Set the user as a RFQ Processor.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function rfqProcessor()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'rfq_processor',
            ];
        });
    }

    /**
     * Set the user as a RFQ Approver.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function rfqApprover()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'rfq_approver',
            ];
        });
    }

    /**
     * Set the user as a LPO Admin.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function lpoAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'lpo_admin',
            ];
        });
    }
}
