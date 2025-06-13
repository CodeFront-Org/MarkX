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
        Schema::table('quote_items', function (Blueprint $table) {
            // Adding unit_pack as a string field since it can be values like "50's", "100's", "pc"
            $table->string('unit_pack')->nullable()->after('item');
            
            // Adding VAT as a decimal field with 2 decimal places
            $table->decimal('vat_rate', 5, 2)->default(16.00)->after('price');
            $table->decimal('vat_amount', 10, 2)->nullable()->after('vat_rate');
            
            // Adding lead_time as a string field since it can be values like "Ex stock"
            $table->string('lead_time')->nullable()->after('vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn(['unit_pack', 'vat_rate', 'vat_amount', 'lead_time']);
        });
    }
};
