<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create two managers
        $managers = [
            [
                'name' => 'Manager One',
                'email' => 'manager1@example.com',
                'role' => 'manager',
            ],
            [
                'name' => 'Manager Two',
                'email' => 'manager2@example.com',
                'role' => 'manager',
            ]
        ];

        foreach ($managers as $manager) {
            User::create(array_merge($manager, [
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]));
        }

        // Create three marketers
        $marketers = [
            [
                'name' => 'Marketer One',
                'email' => 'marketer1@example.com',
                'role' => 'marketer',
            ],
            [
                'name' => 'Marketer Two',
                'email' => 'marketer2@example.com',
                'role' => 'marketer',
            ],
            [
                'name' => 'Marketer Three',
                'email' => 'marketer3@example.com',
                'role' => 'marketer',
            ]
        ];

        foreach ($marketers as $marketer) {
            User::create(array_merge($marketer, [
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]));
        }
    }
}
