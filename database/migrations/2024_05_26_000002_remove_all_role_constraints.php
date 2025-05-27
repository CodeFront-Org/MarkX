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
        // Drop backup table if it exists
        Schema::dropIfExists('users_backup');
        
        // For MySQL, we can directly remove triggers and constraints
        DB::statement('CREATE TABLE users_backup LIKE users');
        DB::statement('INSERT INTO users_backup SELECT * FROM users');
        
        // Get the column names from information schema
        $columns = collect(DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users_backup'"))->pluck('COLUMN_NAME')->toArray();
        
        // Drop the original table
        Schema::dropIfExists('users');
        
        // Recreate the table without any constraints on the role column
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->string('about_me')->nullable();
            $table->string('role')->default('marketer'); // No constraint here
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Copy the data back using explicit column names
        $columnsString = implode(', ', $columns);
        DB::statement("INSERT INTO users ($columnsString) SELECT $columnsString FROM users_backup");
        
        // Drop the backup table
        Schema::dropIfExists('users_backup');
        
        // Test by inserting a finance user
        try {
            $user = new \App\Models\User([
                'name' => 'Test Finance',
                'email' => 'test_finance_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'finance'
            ]);
            $user->save();
            $user->delete(); // Clean up
        } catch (\Exception $e) {
            throw new \Exception("Failed to create finance user: " . $e->getMessage());
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