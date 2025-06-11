<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create two RFQ Approvers
        $managers = [
            [
                'name' => 'RFQ Approver One',
                'email' => 'manager1@example.com',
                'role' => 'rfq_approver',
            ],
            [
                'name' => 'RFQ Approver Two',
                'email' => 'manager2@example.com',
                'role' => 'rfq_approver',
            ]
        ];

        foreach ($managers as $manager) {
            if (!User::where('email', $manager['email'])->exists()) {
                User::create(array_merge($manager, [
                    'password' => bcrypt('password'),
                    'remember_token' => Str::random(10),
                ]));
            }
        }

        // Create three RFQ Processors
        $marketers = [
            [
                'name' => 'RFQ Processor One',
                'email' => 'marketer1@example.com',
                'role' => 'rfq_processor',
            ],
            [
                'name' => 'RFQ Processor Two',
                'email' => 'marketer2@example.com',
                'role' => 'rfq_processor',
            ],
            [
                'name' => 'RFQ Processor Three',
                'email' => 'marketer3@example.com',
                'role' => 'rfq_processor',
            ]
        ];

        foreach ($marketers as $marketer) {
            if (!User::where('email', $marketer['email'])->exists()) {
                User::create(array_merge($marketer, [
                    'password' => bcrypt('password'),
                    'remember_token' => Str::random(10),
                ]));
            }
        }
    }
}
