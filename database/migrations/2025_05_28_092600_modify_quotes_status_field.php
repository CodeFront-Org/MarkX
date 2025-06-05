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
        // The issue is that update_quotes_status_column already adds these columns
        // So we need to check if they exist first
        
        // First add the new timestamp columns to track approvals if they don't exist
        Schema::table('quotes', function (Blueprint $table) {
            // We don't need to add these columns as they're already added in update_quotes_status_column
            // This migration is just to ensure the status field is properly updated
        });

        // Now we need to handle the status field
        // Check if we need to update the status values
        $pendingCount = DB::table('quotes')->where('status', 'pending')->count();
        
        if ($pendingCount > 0) {
            // If we have 'pending' status values, update them to 'pending_manager'
            DB::table('quotes')
                ->where('status', 'pending')
                ->update(['status' => 'pending_manager']);
                
            // Also update other statuses if needed
            DB::table('quotes')
                ->where('status', 'approved')
                ->update(['status' => 'completed']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need for complex down logic since we're just updating values
        // and the columns are managed by other migrations
    }
}; 