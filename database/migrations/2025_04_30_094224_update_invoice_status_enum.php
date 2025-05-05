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
        // First update any existing 'sent' statuses to 'final'
        DB::table('invoices')->where('status', 'sent')->update(['status' => 'final']);

        // In SQLite, we need to create a new table with the updated structure
        Schema::table('invoices', function (Blueprint $table) {
            // Create a temporary column with the new enum values
            $table->string('status_new')->default('draft');
        });

        // Copy data from old status to new status
        DB::table('invoices')->get()->each(function ($invoice) {
            DB::table('invoices')
                ->where('id', $invoice->id)
                ->update(['status_new' => $invoice->status]);
        });

        // Drop the old status column
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Rename the new status column
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        // Add check constraint to mimic enum behavior
        DB::statement("CREATE TRIGGER enforce_invoice_status_enum
            BEFORE INSERT ON invoices
            BEGIN
                SELECT CASE
                    WHEN NEW.status NOT IN ('draft', 'final', 'paid', 'overdue', 'cancelled')
                    THEN RAISE(ABORT, 'Invalid status')
                END;
            END;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any existing 'final' statuses back to 'sent'
        DB::table('invoices')->where('status', 'final')->update(['status' => 'sent']);

        // Drop the trigger
        DB::statement("DROP TRIGGER IF EXISTS enforce_invoice_status_enum;");

        // In SQLite, repeat the process to change the allowed values
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status_old')->default('draft');
        });

        // Copy data
        DB::table('invoices')->get()->each(function ($invoice) {
            DB::table('invoices')
                ->where('id', $invoice->id)
                ->update(['status_old' => $invoice->status]);
        });

        // Drop new status column
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Rename back
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });

        // Add check constraint for original values
        DB::statement("CREATE TRIGGER enforce_invoice_status_enum
            BEFORE INSERT ON invoices
            BEGIN
                SELECT CASE
                    WHEN NEW.status NOT IN ('draft', 'sent', 'paid', 'overdue', 'cancelled')
                    THEN RAISE(ABORT, 'Invalid status')
                END;
            END;");
    }
};
