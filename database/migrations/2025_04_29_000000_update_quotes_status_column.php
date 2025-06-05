<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateQuotesStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, add a new temporary column
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('new_status')->default('pending_manager');
            
            // Only add these columns if they don't exist
            if (!Schema::hasColumn('quotes', 'approved_at')) {
                $table->dateTime('approved_at')->nullable();
            }
            if (!Schema::hasColumn('quotes', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('quotes', 'closed_at')) {
                $table->dateTime('closed_at')->nullable();
            }
            if (!Schema::hasColumn('quotes', 'closed_by')) {
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Update the new column with mapped values
        DB::table('quotes')
            ->where('status', 'pending')
            ->update(['new_status' => 'pending_manager']);

        DB::table('quotes')
            ->where('status', 'approved')
            ->update(['new_status' => 'completed']);

        DB::table('quotes')
            ->where('status', 'rejected')
            ->update(['new_status' => 'rejected']);

        // Drop the old column and rename the new one
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->renameColumn('new_status', 'status');
        });
        
        // No triggers - we'll handle validation in the application code instead
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add a new temporary column
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('old_status')->default('pending');
        });

        // Map the values back
        DB::table('quotes')
            ->where('status', 'pending_manager')
            ->update(['old_status' => 'pending']);

        DB::table('quotes')
            ->where('status', 'completed')
            ->update(['old_status' => 'approved']);

        DB::table('quotes')
            ->where('status', 'rejected')
            ->update(['old_status' => 'rejected']);

        // Drop the current status column and rename the old one back
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->renameColumn('old_status', 'status');
        });
        
        // No triggers - we'll handle validation in the application code instead
    }
} 