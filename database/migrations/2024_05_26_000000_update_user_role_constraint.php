<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateUserRoleConstraint extends Migration
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
            
        // No triggers - we'll handle validation in the application code instead
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing to do here - we're not adding constraints in the migration
    }
} 