<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, let's check if the migrations table exists
        if (Schema::hasTable('migrations')) {
            // Mark the problematic migration as completed if it's not already
            $migration = DB::table('migrations')
                ->where('migration', '2025_04_29_000000_update_quotes_status_column')
                ->first();
                
            if (!$migration) {
                DB::table('migrations')->insert([
                    'migration' => '2025_04_29_000000_update_quotes_status_column',
                    'batch' => DB::table('migrations')->max('batch') + 1
                ]);
            }
        }
        
        // Make sure the quotes table has the right structure
        if (Schema::hasTable('quotes')) {
            // Check if the status column exists with the right type
            if (!Schema::hasColumn('quotes', 'status')) {
                Schema::table('quotes', function (Blueprint $table) {
                    $table->string('status')->default('pending_manager');
                });
            }
            
            // Check if the approval columns exist
            if (!Schema::hasColumn('quotes', 'approved_at')) {
                Schema::table('quotes', function (Blueprint $table) {
                    $table->dateTime('approved_at')->nullable();
                });
            }
            
            if (!Schema::hasColumn('quotes', 'approved_by')) {
                Schema::table('quotes', function (Blueprint $table) {
                    $table->unsignedBigInteger('approved_by')->nullable();
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                });
            }
            
            if (!Schema::hasColumn('quotes', 'closed_at')) {
                Schema::table('quotes', function (Blueprint $table) {
                    $table->dateTime('closed_at')->nullable();
                });
            }
            
            if (!Schema::hasColumn('quotes', 'closed_by')) {
                Schema::table('quotes', function (Blueprint $table) {
                    $table->unsignedBigInteger('closed_by')->nullable();
                    $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to do in down - this is a fix migration
    }
};
