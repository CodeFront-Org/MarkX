<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->boolean('has_rfq')->default(false)->after('valid_until');
            $table->unsignedInteger('rfq_files_count')->default(0)->after('has_rfq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'has_rfq')) {
                $table->dropColumn('has_rfq');
            }
            if (Schema::hasColumn('quotes', 'rfq_files_count')) {
                $table->dropColumn('rfq_files_count');
            }
        });
    }
};
