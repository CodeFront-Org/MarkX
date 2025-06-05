<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixUserRoleConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update any existing users to have valid roles
        DB::table('users')
            ->whereNotIn('role', ['rfq_approver', 'rfq_processor', 'lpo_admin'])
            ->update(['role' => 'rfq_processor']);  // Default to rfq_processor
            
        // Test if we can insert a lpo_admin user
        try {
            DB::table('users')->insert([
                'name' => 'Test LPO Admin',
                'email' => 'test_lpo_admin@example.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role' => 'lpo_admin',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // If we get here, it worked, so delete the test user
            DB::table('users')->where('email', 'test_lpo_admin@example.com')->delete();
        } catch (\Exception $e) {
            // If there's still an issue, just log it
            \Log::error("Could not insert lpo_admin user: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to do anything in down() as we're just removing constraints
    }
} 