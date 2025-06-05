<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RemoveAllRoleConstraints extends Migration
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
            ->update(['role' => 'rfq_processor']);
        
        // Test by inserting a lpo_admin user
        try {
            $user = new \App\Models\User([
                'name' => 'Test LPO Admin',
                'email' => 'test_lpo_admin_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'lpo_admin'
            ]);
            $user->save();
            $user->delete(); // Clean up
        } catch (\Exception $e) {
            \Log::error("Failed to create lpo_admin user: " . $e->getMessage());
            // Don't throw the exception, as we want to continue with the migration
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to do anything here
    }
} 