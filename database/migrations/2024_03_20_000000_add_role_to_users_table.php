<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRoleToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('rfq_processor');
            }
        });

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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
} 