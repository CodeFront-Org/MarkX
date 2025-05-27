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

        // Add check constraint
        DB::statement("CREATE TRIGGER check_quote_status
            BEFORE INSERT ON quotes
            FOR EACH ROW
            BEGIN
                IF NEW.status NOT IN ('pending_manager', 'pending_customer', 'pending_finance', 'completed', 'rejected') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Invalid quote status';
                END IF;
            END;");

        DB::statement("CREATE TRIGGER check_quote_status_update
            BEFORE UPDATE ON quotes
            FOR EACH ROW
            BEGIN
                IF NEW.status NOT IN ('pending_manager', 'pending_customer', 'pending_finance', 'completed', 'rejected') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Invalid quote status';
                END IF;
            END;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the triggers
        DB::statement("DROP TRIGGER IF EXISTS check_quote_status");
        DB::statement("DROP TRIGGER IF EXISTS check_quote_status_update");

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

        // Add check constraint for original values
        DB::statement("CREATE TRIGGER check_quote_status
            BEFORE INSERT ON quotes
            FOR EACH ROW
            BEGIN
                IF NEW.status NOT IN ('pending', 'approved', 'rejected') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Invalid status';
                END IF;
            END;");

        DB::statement("CREATE TRIGGER check_quote_status_update
            BEFORE UPDATE ON quotes
            FOR EACH ROW
            BEGIN
                IF NEW.status NOT IN ('pending', 'approved', 'rejected') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Invalid status';
                END IF;
            END;");
    }
} 